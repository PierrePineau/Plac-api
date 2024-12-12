<?php

namespace App\DataFixtures\Test;

trait TestUserAuthTrait {
    public function authenticate()
    {
        $headers = $this->headers;
        $headers['Content-Type'] = 'application/json';
        $resp = $this->apiManager->api([
            'apiUrl' => $this->apiUrl,
            'path' => 'app/login_check',
            'headers' => $headers,
            'method' => 'JSON',
            'params' => [
                'username' => $this->data['auth']['username'],
                'password' => $this->data['auth']['password'],
            ]
        ]);
        $this->headers['Authorization'] = 'Bearer '.$resp['token'];
    }
}