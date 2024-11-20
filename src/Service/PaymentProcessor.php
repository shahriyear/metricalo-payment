<?php

namespace App\Service;

use App\Dto\PaymentRequest;
use App\Exception\InvalidRequestException;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;
use App\Utils\DataMasker;
use Psr\Log\LoggerInterface;
use App\Service\GatewayRegistry;

class PaymentProcessor
{
    public function __construct(
        private GatewayRegistry $gatewayRegistry,
        private DataMasker $dataMasker,
        private LoggerInterface $logger
    ) {}

    public function process(string $system, PaymentRequest $paymentRequest): array
    {
        $this->logger->info('Processing payment', [
            'gateway' => $system,
            'request_payload' => $this->dataMasker->maskSensitiveData($paymentRequest->toArray()),
        ]);

        $gateway = $this->gatewayRegistry->getGateway($system);
        $this->logger->info('Selected payment gateway', ['gateway_class' => get_class($gateway)]);

        if (!$gateway instanceof PaymentGatewayCanProcessInterface) {
            throw new InvalidRequestException('Gateway does not implement PaymentGatewayCanProcessInterface');
        }

        $response = $gateway->processPayment($paymentRequest);

        $this->logger->info('Received response from payment gateway', [
            'gateway' => $system,
            'response_payload' => $response,
        ]);

        return $response;
    }
}
