<?php

namespace App\EventListener;

use App\Core\Utils\Messenger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HttpExceptionListener implements EventSubscriberInterface
{
    private $messenger;
    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }
    public static function getSubscribedEvents(): array
    {
        return [
            // the priority must be greater than the Security HTTP
            // ExceptionListener, to make sure it's called before
            // the default exception listener
            KernelEvents::EXCEPTION => ['onKernelException', 10],
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $env = $_ENV['APP_ENV'];
        if ($env == 'prod') {
            $exception = $event->getThrowable();
            if (!$exception instanceof HttpException) {
                return;
            }
            $responsedata = $this->messenger->newResponse(false, $exception->getMessage());
            // var_dump($exception);
            // die;
            $event->setResponse(
                new JsonResponse(
                    $responsedata,
                    $exception->getStatusCode()
                )
            );
        }else{
            $this->messenger->errorResponse($event->getThrowable());
            return;
        }
        // optionally set the custom response
        // $event->setCon
        // or stop propagation (prevents the next exception listeners from being called)
        //$event->stopPropagation();
    }

    public function onKernelRequest($event): void
    {
        // try {
        //     $this->messenger->debug('onKernelRequest');
        //     $request = $event->getRequest();

        //     $this->messenger->debug([
        //         'path' => $request->getPathInfo(),
        //         'method' => $request->getMethod(),
        //         'content' => $request->getContent(),
        //         'query' => $request->query->all(),
        //         'headers' => $request->headers->all(),
        //         'resquest' => $request->request->all(),
        //     ]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }
        
        // var_dump($event);
        // die;
    }
}