<?php

namespace App\Service\Plan;

use App\Core\Service\AbstractCoreService;
use App\Entity\Module;
use App\Entity\Plan;
use App\Service\Module\ModuleManager;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;

class PlanManager extends AbstractCoreService
{
    const PLAN_STANDARD = 'STANDARD';
    const PLAN_STANDARD_ANNUAL = 'STANDARD_ANNUAL';
    const PLAN_PRO = 'PRO';
    const PLAN_PRO_ANNUAL = 'PRO_ANNUAL';
    const PLAN_CUSTOM = 'CUSTOM';

    const DEFAULT_MODULES_STANDARD = [
        ModuleManager::MODULE_PROJECT,
        ModuleManager::MODULE_CLIENT,
        ModuleManager::MODULE_NOTE,
        ModuleManager::MODULE_PLANNING,
        ModuleManager::MODULE_EMPLOYE,
    ];

    const DEFAULT_MODULES_PRO = [
        ModuleManager::MODULE_PROJECT,
        ModuleManager::MODULE_CLIENT,
        ModuleManager::MODULE_NOTE,
        ModuleManager::MODULE_PLANNING,
        ModuleManager::MODULE_EMPLOYE,
        ModuleManager::MODULE_TASK,
        ModuleManager::MODULE_EMPLOYE_ACCESS,
    ];

    const DEFAULT_MODULES_CUSTOM = [
        ModuleManager::MODULE_PROJECT,
        ModuleManager::MODULE_CLIENT,
        ModuleManager::MODULE_NOTE,
        ModuleManager::MODULE_PLANNING,
        ModuleManager::MODULE_EMPLOYE,
        ModuleManager::MODULE_TASK,
        ModuleManager::MODULE_EMPLOYE_ACCESS,
    ];

    const PLANS_DATA = [
        self::PLAN_STANDARD => [
            'name' => 'Standard',
            'reference' => self::PLAN_STANDARD,
            'modules' => self::DEFAULT_MODULES_STANDARD,
            'renewalFrequency' => 'monthly',
            'maxDevices' => 2,
            'price' => 40,
            'position' => 1,
        ],
        self::PLAN_STANDARD_ANNUAL => [
            'name' => 'Standard',
            'reference' => 'STANDARD_ANNUAL',
            'modules' => self::DEFAULT_MODULES_STANDARD,
            'renewalFrequency' => 'yearly',
            'maxDevices' => 2,
            'price' => 440, // - 10 %
            'position' => 2,
        ],
        self::PLAN_PRO => [
            'name' => 'Pro',
            'reference' => self::PLAN_PRO,
            'modules' => self::DEFAULT_MODULES_PRO,
            'renewalFrequency' => 'monthly',
            'maxDevices' => 10,
            'price' => 65,
            'position' => 3,
        ],
        self::PLAN_PRO_ANNUAL => [
            'name' => 'Pro',
            'reference' => 'PRO_ANNUAL',
            'modules' => self::DEFAULT_MODULES_PRO,
            'renewalFrequency' => 'yearly',
            'maxDevices' => 10,
            'price' => 715, // - 10 %
            'position' => 4,
        ],
        self::PLAN_CUSTOM => [
            'name' => 'Sur mesure',
            'reference' => self::PLAN_CUSTOM,
            'modules' => self::DEFAULT_MODULES_CUSTOM,
            'renewalFrequency' => null,
            'price' => null,
            'isCustom' => true,
            'position' => 10,
        ],
    ];

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'id',
            'code' => 'Plan',
            'entity' => Plan::class,
        ]);
    }

    public function generateDefault(array $data = [])
    {
        // On récupère les modules déjà existants
        $plans = $this->findAll();

        $refExisting = [];
        foreach ($plans as $pl) {
            $refExisting[] = $pl->getReference();
        }

        $moduleManager = $this->container->get(ModuleManager::class);
        $modules = $moduleManager->findAll();
        
        foreach (self::PLANS_DATA as $key => $data) {
            if (!in_array($key, $refExisting)) {
                $plan = $this->_create($data);

                $filteredModules = array_filter($modules, function ($module) use ($data) {
                    return in_array($module->getReference(), $data['modules']);
                });

                foreach ($filteredModules as $module) {
                    $plan->addModule($module);
                    $this->em->persist($plan);
                }
            }
        }

        $this->em->flush();
    }

    public function _create(array $data)
    {
        $element = new Plan();

        $this->setData(
            $element,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'reference' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'maxDevices' => [
                    'nullable' => true,
                    'type' => 'integer',
                ],
                'position' => [
                    'type' => 'integer',
                ],
                'renewalFrequency' => [
                ],
            ],
            $data
        );

        if (isset($data['isCustom']) && $data['isCustom']) {
            $element->setCustom(true);
        }

        if (!$element->isCustom()) {
            $element->setPrice($data['price']);
        }else{
            $element->setPrice(null);
        }

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }

    public function _update($id, array $data)
    {
        $element = $this->_get($id);

        $this->setData(
            $element,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'description' => [
                    'nullable' => true,
                ],
                'maxDevices' => [
                    'nullable' => true,
                    'type' => 'integer',
                ],
                'position' => [
                    'type' => 'integer',
                ],
                'enabled' => [
                    'type' => 'boolean',
                ],
                'renewalFrequency' => [
                ],
            ],
            $data
        );

        if (isset($data['isCustom']) && $data['isCustom']) {
            $element->setCustom(true);
        }
        
        if (!$element->isCustom()) {
            $element->setPrice($data['price']);
        }else{
            $element->setPrice(null);
        }
        
        $element->getModules()->clear();
        foreach ($data['idsModules'] as $idModule) {
            $element->addModule($this->em->getReference(Module::class, $idModule));       
        }

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }

    public function _delete($id, array $data = [])
    {
        throw new ErrorException('action.not_allowed', 400);
    }
}