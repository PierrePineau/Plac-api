<?php

namespace App\Service\File\Providers;

use App\Core\Interface\FileServiceInterface;
use App\Entity\File;
use App\Service\File\FileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class S3Manager implements FileServiceInterface
{
    private $container;
    private $bucket;
    private $s3;
    private $prefix;

    public const ELEMENT_NOT_FOUND = "File not found";
    public const ELEMENT_FORBIDDEN = "Forbidden path";
    public const FOLDER_FORBIDDEN = "Forbidden folder";
    public const ELEMENT_TYPE_FORBIDDEN = "Forbidden file type";

    public const FOLDER_NOT_FOUND = "Folder not found";

    public const FOLDER_BASE = "organisations/";
    // OTHER
    public const FOLDER_FILES = "files/";
    // OTHER (Ce dossier correspond au fichier "Admin", que l'administateur peut utiliser pour stocker des fichiers NON VISIBLES par les utilisateurs)
    public const FOLDER_ADMIN_FILES = "admin/files/";

    private array $folders = [
        self::FOLDER_FILES,
        self::FOLDER_ADMIN_FILES
    ];

    public function __construct($container, $entityManager)
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
        if ($sourceFile instanceof UploadedFile) {
            $sourceFile = $sourceFile->getPathname();
        }
        $mimeType = mime_content_type($sourceFile);

        if (in_array($mimeType, FileManager::ALLOWED_MIME_TYPES)) {
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
    private function getAbsolutePath(array $options): string
    {
        $folder = $options['folder'] ?? self::FOLDER_FILES;
        $organisation = $options['organisation'];
        $path = $options['path'];
        // On vérifie aussi que le chemin ne commence pas par le préfix ni par "../"
        if (substr($path, 0, 3) === "../" || substr($path, 0, 2) === "./") {
            // On n'accepte pas les chemins relatifs
            throw new NotFoundHttpException($this::ELEMENT_FORBIDDEN);
        }
        
        // On vérifie si le chemin commence par un /, sinon on le retire
        if (substr($path, 0, 1) === "/") {
            $path = substr($path, 1);
        }
        // On vérifie si le chemin commence par le préfix, si oui on le retire
        if (substr($path, 0, strlen($this->prefix)) === $this->prefix) {
            $path = substr($path, strlen($this->prefix));
        }
        // On vérifie si l'organisation est définie
        if (!$organisation) {
            throw new NotFoundHttpException($this::FOLDER_FORBIDDEN);
        }
        $identifier = $organisation->getIdentifier();
        // On vérifie si le chemin commence par l'identifiant de l'organisation
        if (substr($path, 0, strlen($identifier)) === $identifier) {
            $path = substr($path, strlen($identifier));
        }
        // On vérifie que le chemin commence par l'un des dossiers autorisés
        if (!in_array($folder, $this->folders)) {
            throw new NotFoundHttpException($this::FOLDER_FORBIDDEN);
        }
        // organisations/organisation_identifier/folder/path
        $path = self::FOLDER_BASE.$identifier.$folder."/".$path;

        // prod/path
        return $this->prefix.$path;
    }

    /**
	 * Recupère un fichier
	 */
	public function get(array $options, bool $throwException = false): mixed
	{
        try {
            $fullPath = $this->getAbsolutePath($options);
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
	 * Upload un fichier sur le serveur S3
	 */
	public function upload(array $options): void
	{
        $organisation = $options['organisation'];
        // $file = $options['element'] instanceof File ? $options['element'] : null;
        $sourceFile = $options['file'];
        $path = $options['path'];
		if ($sourceFile && $path) {
            $mimeType = $this->getMimeType($sourceFile);
            
            $filePath = $this->getAbsolutePath([
                'organisation' => $organisation,
                'path' => $path
            ]);

			try {
				$this->s3->putObject([
					'Bucket' => $this->bucket,
					'Key' => $filePath,
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
	public function delete(array $options): void
	{
        try {
            $filePath = $this->getAbsolutePath($options);
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $filePath
            ]);
        } catch (\Throwable $th) {
        }
    }

    /**
	 * Copy un fichier
	 */
    public function copy(array $options)
    {
        $sourcePath = $options['sourcePath'];
        $destPath = $options['destPath'];
        $sourcePath = $this->getAbsolutePath([
            'organisation' => $options['organisation'],
            'path' => $sourcePath
        ]);
        $destPath = $this->getAbsolutePath([
            'organisation' => $options['organisation'],
            'path' => $destPath
        ]);

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