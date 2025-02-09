<?php

namespace App\DataFixtures\Core\Status;

use App\DataFixtures\Core\Module\ModuleFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class StatusFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public static function getGroups(): array
    {
        return ['default.generate', 'default.status'];
    }

    public function load(ObjectManager $manager): void
    {
        // On génère dans la BDD le smodules pas default
        $planManager = $this->container->get(PlanManager::class);

        $planManager->generateDefault();
    }
}
