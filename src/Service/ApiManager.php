<?php
namespace App\Service;

class ApiManager
{
    private $container;
    public function __construct($container, $entityManager)
    {
        $this->container = $container;
    }

    public function api($apiUrl, $path, $headers, $method = 'GET', $params = [])
    {
        // $toolsManager = $this->container->get(ToolsManager::class);
        $allowedMethods = ['GET', 'POST', 'DELETE', 'FILE', 'JSON'];
        if(!in_array($method, $allowedMethods)){
            throw new \Exception("METHOD_NOT_ALLOWED");
        }

        // On vérifie que l'api url finit par '/' sinon on l'ajoute
        if(substr($apiUrl, -1) !== '/'){
            $apiUrl .= '/';
        }

        // On vérifie que $path commence par '/' sinon on le retire
        if(substr($path, 0, 1) === '/'){
            $path = substr($path, 1);
        }

        $url = "{$apiUrl}{$path}";
        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ]
        ]);
        $method = strtoupper($method);
        // $toolsManager->debug($url);
        try {
            switch ($method) {
                case 'GET':
                    $request = $client->get($url, [
                        'headers' => $headers,
                        'query' => $params
                    ]);
                    break;
                case 'JSON':
                    // var_dump($url);
                    // var_dump($params);
                    // var_dump($headers);
                    // die();
                    $request = $client->post($url, [
                        'headers' => $headers,
                        'body' => json_encode($params)
                    ]);
                    break;
                case 'POST':
                    // var_dump($url);
                    // var_dump($params);
                    // var_dump($headers);
                    // die();
                    $request = $client->post($url, [
                        'headers' => $headers,
                        'form_params' => $params
                    ]);
                    break;
                case 'DELETE':
                    $request = $client->delete($url, [
                        'headers' => $headers,
                        'form_params' => $params
                    ]);
                    break;
            }
            // On vérifie que le contenu de la réponse est bien du json
            $contentType = $request->getHeader('Content-Type');
            if($contentType[0] == 'application/json'){
                return json_decode((string) $request->getBody(), true);
            }else{
                return (string) $request->getBody();
            }
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            // throw $th;
            $statusCode = $e->getResponse()->getStatusCode();
            $data = json_decode((string) $e->getResponse()->getBody(), true);
            if ($_ENV['APP_ENV'] === 'dev' && $_ENV['APP_DEBUG'] === true) {
                var_dump($data);
                die();
            }
            $message = '';
            switch ($statusCode) {
                case 401:
                    $message = $data['message'] ?? "Vous n'êtes pas autorisé à effectuer cette action";
                    break;
                case 403:
                    $message = $data['message'] ?? "Vous n'êtes pas autorisé à effectuer cette action";
                    break;
                case 500:
                    $message = $data['message'] ?? "Une erreur inconnue est survenue (500)";
                    break;
                case 400:
                    $message = $data['message'] ?? "Une erreur est survenue";
                    break;
                default:
                    $message = $data['message'] ?? "Une erreur inconnue est survenue";
                    break;
            }

            $response = [
                'status' => $statusCode ?? 500,
                'success' => false,
                'message' => $message
            ];
            if (isset($data['code'])) {
                $response['code'] = $data['code'];
            }
            return $response;
        }
        
    }

    public function get($apiUrl, $path, $headers, $params = [])
    {
        return $this->api($apiUrl, $path, $headers, 'GET', $params);
    }

    public function post($apiUrl, $path, $headers, $params = [])
    {
        return $this->api($apiUrl, $path, $headers, 'POST', $params);
    }

    public function json($apiUrl, $path, $headers, $params = [])
    {
        return $this->api($apiUrl, $path, $headers, 'JSON', $params);
    }

    public function delete($apiUrl, $path, $headers, $params = [])
    {
        return $this->api($apiUrl, $path, $headers, 'DELETE', $params);
    }

    public function file($apiUrl, $path, $headers, $params = [])
    {
        return $this->api($apiUrl, $path, $headers, 'FILE', $params);
    }
}