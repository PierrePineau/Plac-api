<?php

namespace App\DataFixtures\Odrazia\App;

use App\Service\App\SettingManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;
    private $doctrine;
    public function __construct($container, $doctrine)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
    }
    public static function getGroups(): array
    {
        return ['plac.app.create', 'setting'];
    }
    public function load(ObjectManager $em): void
    {
        try {
            // On créé les settings par défaut
            // PROMOTIONS
            $settingManager = $this->container->get(SettingManager::class);
            // On vérifie que les promotions n'existent pas

            $resp = $settingManager->create($em, []);

            if ($resp['success'] == false) {
                throw new \Exception($resp['message']);
            }

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
