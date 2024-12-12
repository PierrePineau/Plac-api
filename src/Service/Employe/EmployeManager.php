<?php

namespace App\Service\Employe;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\Employe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EmployeManager extends AbstractCoreService
{
    use OrganisationTrait;

    private $passwordHash;
    public function __construct($container, $entityManager, Security $security, UserPasswordHasherInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'employe',
            'entity' => Employe::class,
            'security' => $security,
        ]);
    }

    // Pour gérer un project il faut que soit défini une organisation
    // Le middleware permet de vérifier si l'organisation est bien défini et si l'utilisateur a les droits
    public function guardMiddleware(array $data): array
    {
        $organisation = $this->getOrganisation($data);

        $data['organisation'] = $organisation;

        return $data;
    }
}