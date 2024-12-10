<?php

namespace App\Service\Plan;

use App\Core\Service\AbstractCoreService;
use App\Entity\Plan;
use Symfony\Bundle\SecurityBundle\Security;

class PlanManager extends AbstractCoreService
{
    const PLAN_STANDARD = 'standard';
    const PLAN_PRO = 'pro';
    const PLAN_ENTERPRISE = 'enterprise';
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Plan',
            'entity' => Plan::class,
            'security' => $security,
        ]);
    }

    public function generateDefault()
    {
        $plans = [
            self::PLAN_STANDARD => 'Standard',
            self::PLAN_PRO => 'Pro',
            self::PLAN_ENTERPRISE => 'Enterprise',
        ];

        $existingsPlans = $this->findBy(['reference' => array_keys($plans)]);
        foreach ($plans as $reference => $name) {
            if (!in_array($reference, $existingsPlans)) {
                $this->_create([
                    'name' => $name,
                    'reference' => $reference,
                ]);
            }
        }

        $this->em->flush();
    }

    public function _search(array $filters = []): array
    {
        $this->getUser();
        var_dump($this->getUser());
        die;

        return parent::_search($filters);
    }

    public function _create(array $data)
    {
        $plan = new Plan();
        $this->setData(
            $plan,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'reference' => [
                    'required' => true,
                    'nullable' => false,
                ],
            ],
            $data
        );

        $this->em->persist($plan);
        $this->isValid($plan);

        return $plan;
    }

    public function _update($id, array $data)
    {
        $plan = $this->find($id);
        if (!$plan) {
            throw new \Exception($this->ELEMENT_NOT_FOUND);
        }

        $this->setData(
            $plan,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'reference' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'enable' => [
                    'required' => true,
                    'nullable' => false,
                    'type' => 'boolean',
                ],
            ],
            $data
        );

        $this->em->persist($plan);
        $this->isValid($plan);

        return $plan;
    }
}