<?php

namespace App\DataFixtures\Core\Module;

use App\Service\Module\ModuleManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ModuleFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public static function getGroups(): array
    {
        return ['default.generate', 'default.module'];
    }

    public function load(ObjectManager $manager): void
    {
        // On génère dans la BDD le smodules pas default
        $moduleManager = $this->container->get(ModuleManager::class);

        $moduleManager->generateDefault();
    }
}
