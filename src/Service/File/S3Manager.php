<?php
namespace App\Service;

use App\Entity\Organisation;
use Aws\Result;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class S3Manager
{
    private $container;
    private $bucket;
    private $s3;
    private $prefix;
    private $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'image/vnd.wap.wbmp',
        'image/bmp',
        'image/tiff',
        'application/pdf',
        'text/csv',
        'text/plain',
        'application/zip',
    ];

    public const ELEMENT_NOT_FOUND = "File not found";
    public const ELEMENT_FORBIDDEN = "Forbidden path";
    public const FOLDER_FORBIDDEN = "Forbidden folder";
    public const ELEMENT_TYPE_FORBIDDEN = "Forbidden file type";

    public const FOLDER_NOT_FOUND = "Folder not found";

    // OTHER
    public const FOLDER_FILES = "files/";
    // OTHER (Ce dossier correspond au fichier "Admin", que l'administateur peut utiliser pour stocker des fichiers NON VISIBLES par les utilisateurs)
    public const FOLDER_ADMIN_FILES = "admin/files/";

    private array $folders = [
        self::FOLDER_FILES,
        self::FOLDER_ADMIN_FILES
    ];

    public function __construct($container)
    {
        $this->container = $container;
        $this->bucket = $_ENV['AWS_S3_BUCKET'];
        // On vérifie si le préfix fini par un /, sinon on l'ajoute
        if (substr($_ENV['AWS_S3_FOLDER_PREFIX'], -1) !== "/") {
            $_ENV['AWS_S3_FOLDER_PREFIX'] = $_ENV['AWS_S3_FOLDER_PREFIX']."/";
        }
        $this->prefix = $_ENV['AWS_S3_FOLDER_PREFIX'];
        $this->s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $_ENV['AWS_S3_REGION'],
            'endpoint' => $_ENV['AWS_S3_ENDPOINT'],
            'use_path_style_endpoint' => false,
            'credentials' => [
                'key' => $_ENV['AWS_S3_KEY'],
                'secret' => $_ENV['AWS_S3_SECRET']
            ],
        ]);
    }

    /**
     * Récupère le myme type d'un fichier, et vérifie si l'exension est autorisée
     */
    private function getMimeType($sourceFile): string
    {
        $mimeType = mime_content_type($sourceFile);

        if (in_array($mimeType, $this->allowedMimeTypes)) {
            return $mimeType;
        } else {
            throw new NotFoundHttpException($this::ELEMENT_TYPE_FORBIDDEN);
        }
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Récupère le chemin absolu d'un fichier
     */
    private function getAbsolutePath(Organisation $organisation, string $path = "/"): string
    {
        // On vérifie aussi que le chemin ne commence pas par le préfix ni par "../"
        if (substr($path, 0, 3) === "../" || substr($path, 0, 2) === "./") {
            // On n'accepte pas les chemins relatifs
            throw new NotFoundHttpException($this::ELEMENT_FORBIDDEN);
        }
        
        // On vérifie si le chemin commence par un /, sinon on le retire
        if (substr($path, 0, 1) === "/") {
            $path = substr($path, 1);
        }
        if (substr($path, 0, strlen($this->prefix)) === $this->prefix) {
            $path = substr($path, strlen($this->prefix));
        }

        if (!$organisation) {
            throw new NotFoundHttpException($this::FOLDER_FORBIDDEN);
        }

        $identifier = $organisation->getIdentifier();

        // On vérifie si le chemin commence par l'identifiant de l'organisation
        if (substr($path, 0, strlen($identifier)) === $identifier) {
            $path = substr($path, strlen($identifier));
        }

        // organisation_identifier/path
        $path = $identifier."/".$path;

        // On vérifie que le chemin commence par l'un des dossiers autorisés
        $found = false;

        foreach ($this->folders as $folder) {
            if (substr($path, 0, strlen($folder)) === $folder) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new NotFoundHttpException($this::FOLDER_FORBIDDEN);
        }

        return $this->prefix.$path."/".$path;
    }
    
    /**
	 * Upload un fichier sur le serveur S3
	 */
	public function uploadFile($sourceFile = null, $destPath = null)
	{
		if ($sourceFile && $destPath) {
            $destPath = $this->getAbsolutePath($organisation, $destPath);

            $mimeType = $this->getMimeType($sourceFile);
			try {
				$this->s3->putObject([
					'Bucket' => $this->bucket,
					'Key' => $destPath,
					'SourceFile' => $sourceFile,
					'ContentType' => $mimeType
				]);
			} catch (\Throwable $th) {
                throw new \Exception($th->getMessage());
			}
		}

	}

    /**
     * Supprime un fichier
     */
	public function deleteFile($filePath)
	{
        try {
            $filePath = $this->getAbsolutePath($filePath);
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $filePath
            ]);
        } catch (\Throwable $th) {
        }
    }

	/**
	 * Recupère un fichier
	 */
	public function getFile($filePath, string $siteCode = null, $throwException = false): mixed
	{
        try {
            $fullPath = $this->getAbsolutePath($filePath, $siteCode ?? null);
            $result = $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key' => $fullPath
            ]);
            return $result;
        } catch (\Throwable $th) {
            if ($throwException) {
                throw new NotFoundHttpException($this::ELEMENT_NOT_FOUND);
            }
            return null;
        }
	}


    /**
	 * Copy un fichier
	 */
    public function copyFile($sourcePath, $destPath)
    {
        $sourcePath = $this->getAbsolutePath($sourcePath);
        $destPath = $this->getAbsolutePath($destPath);

        try {
            $this->s3->copyObject([
                'Bucket' => $this->bucket,
                'CopySource' => $this->bucket."/".$sourcePath,
                'Key' => $destPath
            ]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}