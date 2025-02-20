<?php

namespace App\Service\File;

use App\Core\Service\AbstractCoreService;
use App\Entity\File;
use Symfony\Bundle\SecurityBundle\Security;

class FileManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'File',
            'entity' => File::class,
        ]);
    }

    public function _create(array $data)
    {
        $file = new File();
        $file->setPath($data['path']);
        $file->setMimeType($data['mimeType']);
        
    }

    public function _update($id, array $data)
    {
        
    }
}