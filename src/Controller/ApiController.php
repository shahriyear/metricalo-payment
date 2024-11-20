<?php

namespace App\Controller;

use App\Dto\PaymentRequest;
use App\Factory\PaymentRequestFactory;
use App\Service\PaymentProcessor;
use App\Schema\PaymentRequestSchema;
use App\Service\JsonSchemaValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController
{
    #[Route('/app/example/{system}', methods: ['POST'])]
    public function handleRequest(string $system, Request $request, JsonSchemaValidator $validator,  PaymentRequestFactory $paymentRequestFactory, PaymentProcessor $paymentProcessor): JsonResponse
    {
        $data = json_decode($request->getContent());
        $validator->validate($data, PaymentRequestSchema::class);

        $paymentRequest = $paymentRequestFactory->create($data);
        $response = $paymentProcessor->process($system, $paymentRequest);

        return new JsonResponse($response, $response['code']);
    }
}
