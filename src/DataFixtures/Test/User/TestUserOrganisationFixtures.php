<?php

namespace App\DataFixtures\Test\User;

use App\Core\Utils\Messenger;
use App\DataFixtures\Test\TestUserAuthTrait;
use App\Service\ApiManager;
use App\Service\User\UserManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TestUserOrganisationFixtures extends Fixture implements FixtureGroupInterface
{
    private $container;
    private $apiManager;
    private $messenger;
    private $apiUrl;
    private $headers;
    private $data;
    private $actions;

    use TestUserAuthTrait;

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
            'auth' => [
                'username' => 'test@gmail.com',
                'password' => 'test123A',
            ],
            'idUser' => "0193bb21-45bd-7047-a092-5d55be3f648a",
        ];
        $this->actions = [
            'Authenticate' => 'authenticate',
            'Create new organisation' => 'createUserOrganisation',
            // 'Update info user' => 'updateInfoUser',
            // 'Delete user' => 'deleteUser',
        ];
    }

    public static function getGroups(): array
    {
        return ['test.generate', 'test.user.organisation'];
    }

    public function load(ObjectManager $em): void
    {
        try {
            foreach ($this->actions as $name => $function) {
                try {
                    $resp = $this->$function();

                    if (isset($resp['success']) && $resp['success'] != true) {
                        $this->messenger->debug($resp);
                        throw new \Exception($resp['message']);
                    }

                    $resp['message'] = $resp['message'] ?? '';
                    $this->messenger->debug("RUN TEST : {$name} : OK ({$resp['message']})");

                } catch (\Throwable $th) {
                    //throw $th;
                    $this->messenger->log("RUN TEST : {$name} : K.O ({$th->getMessage()})");
                    dump($th->getLine());
                    dump($th->getFile());
                    break;
                }
            }

        } catch (\Throwable $th) {
            $this->logger->error($th->getMessage());
            // throw $th;
        }
    }

    private function createUserOrganisation():array
    {
        $resp = $this->apiManager->api([
            'apiUrl' => $this->apiUrl,
            'path' => 'app/users/' . $this->data['idUser'] . '/organisations',
            'headers' => $this->headers,
            'method' => 'POST',
            'params' => [
                'name' => 'Test Organisation',
            ]
        ]);
        
        $this->messenger->debug($resp);

        return $resp;
    }
}
