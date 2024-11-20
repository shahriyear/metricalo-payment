<?php

namespace App\Gateway;

use App\Dto\PaymentRequest;
use App\Gateway\Core\PaymentGatewayAbstract;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

class AciGateway extends PaymentGatewayAbstract implements PaymentGatewayCanProcessInterface
{
    public function processPayment(PaymentRequest $paymentRequest): array
    {
        $apiUrl = $this->getApiUrl();
        $payload = $this->getPayload($paymentRequest);
        $headers = $this->getHeaders();

        $response = $this->httpClient->post($apiUrl, $payload, $headers);

        return $this->responseHandler($response);
    }

    private function responseHandler(array $response): array
    {

        if ($response['status'] === 'error') {
            //for simplicity just returning the error
            return $response;
        }

        //might check params for success or failed
        $content = $response['data'];
        return [
            'status' => 'success',
            'code' => $response['code'],
            'data' => [
                'transactionId' => $content['id'],
                'createdAt' => date('Y-m-d H:i:s T', strtotime($content['timestamp'])),
                'amount' => $content['amount'],
                'currency' => $content['currency'],
                'cardBin' => $content['card']['bin'],
            ]
        ];
    }

    private function getPayload(PaymentRequest $paymentRequest): array
    {
        return [
            'entityId' => $this->configs['entityId'],
            'amount' => $paymentRequest->getAmount(),
            'currency' => $paymentRequest->getCurrency(),
            'paymentBrand' => 'VISA', // hardcoded for simplicity can be filter via bin
            'paymentType' => 'DB',
            'card.number' => $paymentRequest->getCardNumber(),
            'card.expiryMonth' => $paymentRequest->getCardExpMonth(),
            'card.expiryYear' => $paymentRequest->getCardExpYear(),
            'card.cvv' => $paymentRequest->getCardCvv()
        ];
    }

    private function getHeaders(): array
    {
        return [
            'Authorization:Bearer ' . $this->generateToken(),
        ];
    }

    private function generateToken(): string
    {
        return 'OGE4Mjk0MTc0YjdlY2IyODAxNGI5Njk5MjIwMDE1Y2N8c3k2S0pzVDg=';
    }

    private function getApiUrl(): string
    {
        $base = rtrim($this->configs['apiUrl'], '/');
        $version = $this->configs['apiVersion'];

        return "$base/$version/payments";
    }
}
