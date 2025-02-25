<?php

namespace App\Service\Access;

use App\Entity\Access;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccessManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'Access',
            'entity' => Access::class,
        ]);
    }

    // TODO : Génération d'un QRCode pour l'accès
    public function _create(array $data)
    {
        $access = new Access();

        $entity = $data['entity'];
        $access->setEntityId($entity->getId());
        // Je voudrais check si $entity possède une méthode getIdentifier() ou getId()
        if (method_exists($entity, 'getUuid')) {
            $access->setEntityIdentifier($entity->getUuid());
        } else {
            $access->setEntityIdentifier($entity->getId());
        }
        switch (get_class($entity)) {
            case 'App\Entity\Client':
                $access->setType('Client');
                break;
            case 'App\Entity\Address':
                $access->setType('Address');
                break;
            case 'App\Entity\Organisation':
                $access->setType('Organisation');
                break;
            case 'App\Entity\Project':
                $access->setType('Project');
                break;
            default:
                throw new \Exception('Type not found');
                break;
        }
        $this->em->persist($access);
        $this->isValid($access);

        return $access;
    }

    public function _get($id, array $filters = []): mixed
    {
        $access = $this->findOneBy(['uuid' => $filters['token']]);

        if (!$access) {
            throw new NotFoundHttpException($this->ELEMENT_NOT_FOUND);
        }

        return $access;
    }
}