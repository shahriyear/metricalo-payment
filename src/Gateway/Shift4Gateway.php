<?php

namespace App\Gateway;

use App\Dto\PaymentRequest;
use App\Gateway\Core\PaymentGatewayAbstract;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

class Shift4Gateway extends PaymentGatewayAbstract implements PaymentGatewayCanProcessInterface
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
                'createdAt' => date('Y-m-d H:i:s T', $content['created']),
                'amount' => $content['amount'] / 100,
                'currency' => $content['currency'],
                'cardBin' => $content['card']['first6'],
            ]
        ];
    }


    private function getPayload(PaymentRequest $paymentRequest): array
    {
        return [
            'amount' => $paymentRequest->getAmount() * 100,
            'currency' => $paymentRequest->getCurrency(),
            'card' => [
                'number' => $paymentRequest->getCardNumber(),
                'expMonth' => $paymentRequest->getCardExpMonth(),
                'expYear' => $paymentRequest->getCardExpYear(),
                'cvc' => $paymentRequest->getCardCvv(),
            ]
        ];
    }

    private function getHeaders(): array
    {
        return [
            "Authorization: Basic " . $this->generateToken(),
            "Content-Type: application/json"
        ];
    }

    private function generateToken(): string
    {
        $apiKey = $this->configs['apiKey'];
        return base64_encode("$apiKey:");
    }

    private function getApiUrl(): string
    {
        $base = rtrim($this->configs['apiUrl'], '/');
        return "$base/charges";
    }
}
