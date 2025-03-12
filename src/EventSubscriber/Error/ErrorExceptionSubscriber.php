<?php
namespace App\EventSubscriber\Error;

use App\Core\Utils\Messenger;
use App\Entity\Admin;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ErrorExceptionSubscriber implements EventSubscriberInterface
{
    private $container;
	private $messenger;
    public function __construct($container)
    {
        $this->container = $container;
		$this->messenger = $this->container->get(Messenger::class);
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            ExceptionEvent::class => [
				['onKernelException', 10],
			],
        ];
    }

    public function onKernelException(ExceptionEvent $event): ExceptionEvent
    {
        $env = $_ENV['APP_ENV'];
        if ($env == 'prod') {
            $exception = $event->getThrowable();
            if (!$exception instanceof HttpException) {
                return $event;
            }
            $responsedata = $this->messenger->newResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
            $event->setResponse(
                new JsonResponse(
                    $responsedata,
                    $exception->getStatusCode()
                )
            );
        }else{
            $this->messenger->errorResponse($event->getThrowable());
        }

		return $event;
    }
}