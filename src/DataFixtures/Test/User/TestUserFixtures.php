<?php

namespace App\DataFixtures\Test\User;

use App\Core\Utils\Messenger;
use App\Service\ApiManager;
use App\Service\User\UserManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TestUserFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;
    private $apiManager;
    private $messenger;
    private $apiUrl;
    private $headers;
    private $data;
    private $actions;

    public function __construct($container)
    {
        $this->container = $container;
        $this->apiManager = $this->container->get(ApiManager::class);
        $this->messenger = $this->container->get(Messenger::class);
        $this->apiUrl = 'https://https://127.0.0.1:8000/api/v1';
        $this->headers = [
            'Accept' => 'application/json',
        ];
        $this->data = [];
        $this->actions = [
            'Create new user' => 'createUser',
            'Authenticate' => 'authenticate',
            'Update info user' => 'updateInfoUser',
            'Delete user' => 'deleteUser',
        ];
    }

    public static function getGroups(): array
    {
        return ['test.generate', 'test.user'];
    }

    public function load(ObjectManager $em): void
    {
        try {
            $messenger = $this->container->get(Messenger::class);
            foreach ($this->actions as $name => $function) {
                try {
                    $resp = $this->$function();

                    if ($resp['success'] != true) {
                        throw new \Exception($resp['message']);
                    }

                    $resp['message'] = $resp['message'] ?? '';
                    $messenger->debug("RUN TEST : {$name} : OK ({$resp['message']})");

                } catch (\Throwable $th) {
                    //throw $th;
                    $messenger->log("RUN TEST : {$name} : K.O");
                    $messenger->log($th->getMessage());

                    break;
                }
            }
            
            // $em->remove($cart);

        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            // throw $th;
        }
    }

    private function createUser():array
    {
        $resp = $this->apiManager->post(
            $this->apiUrl,
            'app/users',
            $this->headers,
            'POST',
            [
                'email' => 'demo@gmail.com',
                'password' => 'demo123A',
            ]
        );
        
        $this->messenger->debug($resp);

        return $resp;
    }

    private function autenticate():array
    {
    }
}
