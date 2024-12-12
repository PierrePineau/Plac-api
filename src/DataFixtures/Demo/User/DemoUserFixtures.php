<?php

namespace App\DataFixtures\Demo\User;

use App\Core\Utils\Messenger;
use App\Service\User\UserManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DemoUserFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;
    private $messenger;

    public function __construct($container)
    {
        $this->container = $container;
        $this->messenger = $this->container->get(Messenger::class);
    }

    public static function getGroups(): array
    {
        return ['demo.generate', 'demo.user'];
    }

    public function load(ObjectManager $manager): void
    {
        // On génère dans la BDD le smodules pas default
        $userManager = $this->container->get(UserManager::class);

        $resp = $userManager->create([
            'email' => 'demo@gmail.com',
            'password' => 'demo123A',
        ]);

        $this->messenger->debug($resp);
    }
}
