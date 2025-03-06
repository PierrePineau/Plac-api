<?php

namespace App\Service\File;

use App\Core\Service\AbstractCoreService;
use App\Entity\File;
use App\Service\File\Providers\S3Manager;
use App\Service\Organisation\OrganisationManager;
use App\Service\Project\ProjectFileManager;
use App\Service\Project\ProjectManager;
use Symfony\Bundle\SecurityBundle\Security;

class FileManager extends AbstractCoreService
{
    // OTHER
    public const FOLDER_FILES = "files/";
    // OTHER (Ce dossier correspond au fichier "Admin", que l'administateur peut utiliser pour stocker des fichiers NON VISIBLES par les utilisateurs)
    public const FOLDER_ADMIN_FILES = "admin/files/";
    
    public const ALLOWED_MIME_TYPES = [
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
        'application/x-rar-compressed',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'video/mp4',
        'video/quicktime',
    ];

    public const ALLOWED_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg',
        'mp4',
        'avi',
        'mov',
        'mp3',
        'wav',
        'zip',
        'rar',
    ];

    public const TYPE_FILE = 'FILE';
    public const FILE_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'zip',
        'rar',
    ];

    public const TYPE_MEDIA = 'MEDIA';
    public const MEDIA_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg',
        'mp4',
        'avi',
        'mov',
        'mp3',
        'wav',
    ];

    const GATEWAYS = [
        'OCEAN_S3_BUCKET' => S3Manager::class,
    ];

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'File',
            'entity' => File::class,
        ]);
    }

    public function _create(array $data)
    {
        $element = new File();

        $name = $data['name'] ?? null;
        $data['name'] = explode('.', $name)[0] ?? $name;
        
        // On fait un explode pour récupérer l'extension du fichier
        $extension = explode('.', $name);
        $extension = end($extension);
        $extension = $data['ext'] ?? $extension;
        $extension = explode('.', $extension);
        $extension = end($extension);
        $extension = strtolower($extension);
        $data['ext'] = $extension;

        // On vérifie si l'extension est autorisée
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception($this->ELEMENT_NOT_ALLOWED);
        }

        if (in_array($extension, self::FILE_EXTENSIONS)) {
            $data['type'] = self::TYPE_FILE;
        } elseif (in_array($extension, self::MEDIA_EXTENSIONS)) {
            $data['type'] = self::TYPE_MEDIA;
        }

        $this->setData(
            $element,
            [
                'name' => [
                    'nullable' => true,
                ],
                'ext' => [
                    'nullable' => true,
                ],
                'type' => [
                    'nullable' => true,
                ],
            ],
            $data
        );

        $element->setUrl(uniqid() . '.' . $extension);
        $this->em->persist($element);
        $this->isValid($element);
        
        $file = $element;
        $fileSize = filesize($data['file']);
        $file->setSize($fileSize);
        if (isset($data['idProject']) || isset($data['idsProject'])) {
            $ids = $data['idsProject'] ?? [$data['idProject']];
            $ProjectFileManager = $this->container->get(ProjectFileManager::class);
            $projectManager = $this->container->get(ProjectManager::class);
            
            $ProjectFileManager->_add([
                'by' => ProjectFileManager::BY_FILE,
                'projects' => $projectManager->findByIds($ids), // uuids
                'file' => $element,
            ]);
        }
        
        // On upload le fichier via le provider
        $provider = $this->container->get(self::GATEWAYS['OCEAN_S3_BUCKET']);
        $provider->upload([
            'organisation' => $data['organisation'],
            'file' => $data['file'],
            'folder' => self::FOLDER_FILES,
            'path' => $file->getPath()
        ]);

        return $element;
    }

    public function _update($id, array $data)
    {
        $element = $this->_get($id);

        if (isset($data['name'])) {
            // On retire l'exension du nom du fichier
            $ext = $element->getExt();
            $name = explode($ext, $element->getName());
            $name = $name[0];
            $data['name'] = $name;
        }

        $this->setData(
            $element,
            [
                'name' => [
                    'nullable' => false,
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }

    public function _delete($id, array $data = [])
    {
        $element = $this->_get($id);

        // On check si le fichier à des associations

        // Organisations
        $element->getOrganisationFiles()->clear();
        // Projects
        $element->getProjectFiles()->clear();
        $this->em->persist($element);

        $provider = $this->container->get(self::GATEWAYS['OCEAN_S3_BUCKET']);
        $provider->delete([
            'organisation' => $data['organisation'],
            'folder' => self::FOLDER_FILES,
            'path' => $element->getPath(),
        ]);

        $this->em->remove($element);
        $this->em->flush();

        return true;
    }

    public function get($id, array $filters = []): ?array
    {
        // 0194f783-58bc-7f20-a76a-6a826fe51bd1/fichiers/
        // 67c5dfb9b9b0e.jpg
        try {
            $element = $this->findOneBy(['url' => $id]);
            $idOrganisation = $filters['idOrganisation'] ?? null;
            // $organisation = $this->container->get(OrganisationManager::class)->find($idOrganisation);
            if (empty($element)) {
                throw new \Exception($this->ELEMENT_NOT_FOUND);
            }
            $provider = $this->container->get(self::GATEWAYS['OCEAN_S3_BUCKET']);

            $file = $provider->get([
                'organisation' => $idOrganisation,
                'folder' => self::FOLDER_FILES,
                'path' => $element->getPath(),
            ]);
    
            return $this->messenger->newResponse(
                [
                    'success' => true,
                    'message' => $this->ELEMENT_FOUND,
                    'code' => 200,
                    'data' => [
                        'file' => $element->toArray(),
                        'content' => $file ?? null,
                    ]
                ]
            );
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }
}