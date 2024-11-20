<?php

namespace App\EventListener;

use JsonSchema\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ValidationExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onValidationException', 100],
        ];
    }
    public function onValidationException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {
            $response = new JsonResponse([
                'status' => 'error',
                'errors' => explode(', ', $exception->getMessage()),
            ], Response::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        }
    }
}
