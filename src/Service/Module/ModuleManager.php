<?php

namespace App\Service\Module;

use App\Core\Service\AbstractCoreService;
use App\Entity\Module;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;

class ModuleManager extends AbstractCoreService
{
    const MODULE_PROJECT = 'PROJECT';
    const MODULE_PLANNING = 'PLANNING';
    const MODULE_CLIENT = 'CLIENT';
    const MODULE_EMPLOYE = 'EMPLOYE';
    const MODULE_NOTE = 'NOTE';
    const MODULE_TASK = 'TASK';
    const MODULE_EMPLOYE_ACCESS = 'EMPLOYE_ACCESS';
    const MODULES = [
        self::MODULE_PROJECT,
        self::MODULE_PLANNING,
        self::MODULE_CLIENT,
        self::MODULE_EMPLOYE,
        self::MODULE_NOTE,
        self::MODULE_TASK,
        self::MODULE_EMPLOYE_ACCESS,
    ];

    const MODULES_DATA = [
        self::MODULE_PROJECT => [
            'name' => 'Chantiers',
            'reference' => self::MODULE_PROJECT,
            'position' => 1,
        ],
        self::MODULE_PLANNING => [
            'name' => 'Planning',
            'reference' => self::MODULE_PLANNING,
            'position' => 2,
        ],
        self::MODULE_CLIENT => [
            'name' => 'Clients',
            'reference' => self::MODULE_CLIENT,
            'position' => 3,
        ],
        self::MODULE_EMPLOYE => [
            'name' => 'Employés',
            'reference' => self::MODULE_EMPLOYE,
            'position' => 4,
        ],
        self::MODULE_NOTE => [
            'name' => 'Notes',
            'reference' => self::MODULE_NOTE,
            'position' => 5,
        ],
        self::MODULE_TASK => [
            'name' => 'Tâches',
            'reference' => self::MODULE_TASK,
            'position' => 6,
        ],
        self::MODULE_EMPLOYE_ACCESS => [
            'name' => 'Accès employé',
            'reference' => self::MODULE_EMPLOYE_ACCESS,
            'position' => 7,
        ],
    ];

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'id',
            'code' => 'Module',
            'entity' => Module::class,
            'security' => $security,
        ]);
    }

    public function generateDefault(array $data = [])
    {
        $newModules = [];
        
        // On récupère les modules déjà existants
        $modules = $this->findAll();

        $refExisting = [];
        foreach ($modules as $module) {
            $refExisting[] = $module->getReference();
        }

        foreach (self::MODULES_DATA as $ref => $module) {
            if (!in_array($ref, $refExisting)) {
                $this->_create(self::MODULES_DATA[$ref]);
            }
        }

        $this->em->flush();
        return $newModules;
    }

    public function _create(array $data)
    {
        $module = new Module();

        $this->setData(
            $module,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'reference' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'position' => [
                    'required' => true,
                    'nullable' => false,
                    'type' => 'int',
                ],
            ],
            $data
        );
        $module->setEnabled(true);

        $this->em->persist($module);
        $this->isValid($module);

        return $module;
    }

    public function _update($id, array $data)
    {
        $module = $this->_get($id);

        $this->setData(
            $module,
            [
                'name' => [
                    'nullable' => false,
                ],
                'enabled' => [
                    'type' => 'boolean',
                ],
                'position' => [
                    'nullable' => false,
                    'type' => 'integer',
                ],
            ],
            $data
        );

        $this->em->persist($module);
        $this->isValid($module);

        return $module;
    }

    public function _delete($id, array $data = [])
    {
        throw new ErrorException('action.not_allowed', 400);
    }
}