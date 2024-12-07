<?php

namespace App\DataFixtures\Odrazia\App;

use App\Service\App\LanguageManager;
use App\Service\App\SettingManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class LanguageFixtures extends Fixture implements FixtureGroupInterface
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
        return ['plac.app.create', 'language'];
    }
    public function load(ObjectManager $em): void
    {
        try {
            // On créé les languages par défaut
            $langManager = $this->container->get(LanguageManager::class);
            // On vérifie que les langs n'existent pas
            $langs = [
                'fr' => 'Français',
                'en' => 'Anglais',
                'es' => 'Espagnol'
            ];

            foreach ($langs as $code => $name) {
                $langExist = $langManager->findOneByFilters($em, ['code' => $code]);
                if ($langExist) {
                    continue;
                }

                $data = [
                    'code' => $code,
                    'name' => $name,
                    'default' => $code == 'fr' ? true : false,
                    'active' => $code == 'es' ? 'false' : 'true'
                ];
                $resp = $langManager->create($em, $data);
                if ($resp['success'] == false) {
                    throw new \Exception($resp['message']);
                }
            }

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
