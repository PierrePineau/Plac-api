<?php

namespace App\Core\Utils;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

#[WithMonologChannel('plac')]
class Messenger
{
    private $container;
    public ?LoggerInterface $logger;
    private $translator;
    private $eventDispatcher;

    public function __construct($container, $logger, TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    // Get message from code with translation
    public function getMessage($code, array $data = []): string
    {
        $parameters = [];
        $domain = null;
        $locale = null;
        if (isset($data['parameters'])) {
            $parameters = $data['parameters'];
        }
        if (isset($data['domain'])) {
            $domain = $data['domain'];
        }
        if (isset($data['locale'])) {
            $locale = $data['locale'];
        }
        return $this->translator->trans($code, $parameters, $domain, $locale);
    }

    public function debug(mixed $data, array $context = []): void
    {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }
        // Do this where you want to log the line and file
        // if (empty($context)) {
        //     $context = [
        //         'line', __LINE__,
        //         'file', __FILE__,
        //     ];
        // }
        $this->logger->debug($data, $context);
    }

    public function log(mixed $data, array $context = [], string $level = 'error'): void
    {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }

        if ($level == 'error') {
            $this->logger->error($data, $context);
        } else {
            $this->logger->info($data, $context);
        }
    }

    // Get message from code with translation
    public function errorResponse(Throwable $th): array
    {
        return $this->newResponse([
            'success' => false,
            'message' => $th->getMessage(),
            'code' => $th->getCode(),
            'th' => $th,
        ]);
    }

    // Get message from code with translation
    public function newResponse(array $params = []): array
    {
        $success = isset($params['success']) && $params['success'] === true ? true : false;
        $message = $params['message'] ?? '';
        $data = $params['data'] ?? null;
        $th = isset($params['th']) && $params['th'] instanceof Throwable ? $params['th'] : null;
        $code = isset($params['code']) ? $params['code'] : 200;
        // On recherche si la string contient "||"
        if (strpos($message, '||')) {
            // On sépare la string en tableau
            $message = explode('||', $message);
        }

        if (is_array($message)) {
            $stringMessage = '';
            foreach ($message as $singleMessage) {
                $stringMessage .= $this->getMessage($singleMessage) . '. ';
            }
            $message = $stringMessage;
        }

        if ($code == 0) {
            // On considère que c'est une erreur interne
            $code = 500;
            $message = 'error.internal';
        }
        $response = [
            'success' => $success,
            'code' => $code,
            'codeMessage' => $message,
            'message' => $this->getMessage($message),
        ];
        if (isset($data)) {
            $response['data'] = $data;
        }

        if ($th) {
            if ($_ENV['APP_ENV'] == 'dev') {
                $response['th'] = [
                    'message' => $th->getMessage(),
                    'code' => $th->getCode(),
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ];
            }
            $this->logger->error($message, [
                'exception' => $th,
                'channel' => 'main',
            ]);
        }

        return $response;
    }

    public function dispatchEvent(Event $event, ?string $eventName = null): ?Event
    {
        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}
