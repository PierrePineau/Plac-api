<?php

namespace App\DataFixtures\Core\Plan;

use App\DataFixtures\Core\Module\ModuleFixtures;
use App\Service\Plan\PlanManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getDependencies(): array
    {
        return [
            ModuleFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['default.generate', 'default.plan'];
    }

    public function load(ObjectManager $manager): void
    {
        // On génère dans la BDD le smodules pas default
        $planManager = $this->container->get(PlanManager::class);

        $planManager->generateDefault();
    }
}
