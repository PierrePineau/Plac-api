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
        $this->apiUrl = 'https://127.0.0.1:8000/api/';
        $this->headers = [
            'Accept' => 'application/json',
            // 'Content-Type' => 'application/json',
        ];
        $this->data = [
            'testUser' => [
                'username' => 'test@gmail.com',
                'password' => 'test123A',
            ],
        ];
        $this->actions = [
            'Create new user' => 'createUser',
            'Authenticate' => 'authenticate',
            // 'Update info user' => 'updateInfoUser',
            // 'Delete user' => 'deleteUser',
        ];
    }

    public static function getGroups(): array
    {
        return ['test.generate', 'test.user'];
    }

    public function load(ObjectManager $em): void
    {
        try {
            foreach ($this->actions as $name => $function) {
                try {
                    $resp = $this->$function();

                    if ($resp['success'] != true) {
                        throw new \Exception($resp['message']);
                    }

                    $resp['message'] = $resp['message'] ?? '';
                    $this->messenger->debug("RUN TEST : {$name} : OK ({$resp['message']})");

                } catch (\Throwable $th) {
                    //throw $th;
                    $this->messenger->log("RUN TEST : {$name} : K.O ({$th->getMessage()})");
                    // dump($th->getLine());
                    // dump($th->getFile());
                    // $messenger->log($th->getMessage());
                    break;
                }
            }

        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            // throw $th;
        }
    }

    private function createUser():array
    {
        $resp = $this->apiManager->api([
            'apiUrl' => $this->apiUrl,
            'path' => 'app/users',
            'headers' => $this->headers,
            'method' => 'POST',
            'params' => [
                'email' => $this->data['testUser']['email'],
                'password' => $this->data['testUser']['password'],
            ]
        ]);
        
        $this->messenger->debug($resp);

        return $resp;
    }

    private function authenticate():array
    {
        // $resp = $this->apiManager->post(
        //     $this->apiUrl,
        //     'app/login_check',
        //     $this->headers,
        //     'POST',
        //     [
        //         'email' => $this->data['testUser']['email'],
        //         'password' => $this->data['testUser']['password'],
        //     ]
        // );
        $this->messenger->debug($this->apiUrl. 'app/users');
        $headers = $this->headers;
        $headers['Content-Type'] = 'application/json';
        $resp = $this->apiManager->api([
            'apiUrl' => $this->apiUrl,
            'path' => 'app/login_check',
            'headers' => $headers,
            'method' => 'JSON',
            'params' => [
                'username' => $this->data['testUser']['email'],
                'password' => $this->data['testUser']['password'],
            ]
        ]);
        $this->headers['Authorization'] = 'Bearer '.$resp['token'];
    }
}
