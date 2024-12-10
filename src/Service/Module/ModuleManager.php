<?php

namespace App\Service\Module;

use App\Core\Service\AbstractCoreService;
use App\Entity\Module;
use Symfony\Bundle\SecurityBundle\Security;

class ModuleManager extends AbstractCoreService
{
    public const MODULE_DEFAULT = 'default';
    public const MODULE_MESSAGE = 'message';
    public const MODULE_ACCESS_EMPLOYES = 'access_employes';
    public const MODULE_PLANNING = 'planning';

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Module',
            'entity' => Module::class,
            'security' => $security,
        ]);
    }

    // On génère dans la BDD les modules pas default
    public function generateDefault()
    {
        $modules = [
            self::MODULE_DEFAULT => 'Par défaut',
            self::MODULE_MESSAGE => 'Messagerie',
            self::MODULE_ACCESS_EMPLOYES => 'Accès employés',
            self::MODULE_PLANNING => 'Planning',
        ];

        $existingsModules = $this->findBy(['reference' => array_keys($modules)]);
        foreach ($modules as $reference => $name) {
            if (!in_array($reference, $existingsModules)) {
                $module = $this->_create([
                    'name' => $name,
                    'reference' => $reference,
                ]);
            }
        }

        $this->em->flush();
    }

    public function _create(array $data)
    {
        $newModule = new Module();

        $this->setData(
            $newModule,
            [
                'name' => [
                    'required' => true,
                ],
                'reference' => [
                    'required' => true,
                    'nullable' => false,
                ],
            ],
            $data,
        );

        $this->em->persist($newModule);
        $this->isValid($newModule);

        return $newModule;
    }

    public function _update($id, array $data)
    {
        $module = $this->find($id);
        if (!$module) {
            throw new \Exception($this->ELEMENT_NOT_FOUND);
        }

        $this->setData(
            $newModule,
            [
                [
                    'name' => [
                        'required' => true,
                        'nullable' => false,
                    ],
                ],
                [
                    'reference' => [
                        'required' => true,
                        'nullable' => false,
                    ],
                ],
                [
                    'enable' => [
                        'required' => true,
                        'nullable' => false,
                        'type' => 'boolean',
                    ],
                ],

            ],
            $data,
        );

        $this->em->persist($newModule);
        $this->isValid($newModule);

        return $newModule;
    }
}