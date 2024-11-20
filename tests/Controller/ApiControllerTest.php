<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testHandleRequestWithValidInputUsingShift4Gateway(): void
    {
        $client = static::createClient();

        $client->request('POST', '/app/example/shift4', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'amount' => 100.0,
            'currency' => 'USD',
            'cardNumber' => '4111111111111111',
            'cardExpMonth' => 12,
            'cardExpYear' => 2025,
            'cardCvv' => '123',
        ]));

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('success', $content['status']);
        $this->assertEquals('USD', $content['data']['currency']);
    }

    public function testHandleRequestWithMissingRequiredFields(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/app/example/shift4',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'cardNumber' => '4111111111111111',
                'cardExpMonth' => 12,
                'cardExpYear' => 2025,
                'cardCvv' => '123',
            ])
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('error', $content['status']);
        $this->assertArrayHasKey('errors', $content);
        $this->assertContains('amount: The property amount is required', $content['errors']);
        $this->assertContains('currency: The property currency is required', $content['errors']);
    }


    public function testHandleRequestWithInvalidInput(): void
    {
        $client = static::createClient();

        // Missing required fields like `currency`, `cardNumber`, etc.
        $client->request(
            'POST',
            '/app/example/shift4',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'amount' => -10, // Invalid amount
                'cardNumber' => '123', // Invalid card number
            ])
        );

        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('error', $content['status']);
        $this->assertArrayHasKey('errors', $content);

        $this->assertContains('currency: The property currency is required', $content['errors']);
        $this->assertContains('amount: Must have a minimum value greater than or equal to 0', $content['errors']);
        $this->assertContains('cardNumber: Must be at least 12 characters long', $content['errors']);
    }

    public function testHandleRequestWithUnsupportedGateway(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/app/example/unknown_gateway', // Non-existent gateway
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'amount' => 100.0,
                'currency' => 'USD',
                'cardNumber' => '4111111111111111',
                'cardExpMonth' => 12,
                'cardExpYear' => 2025,
                'cardCvv' => '123',
            ])
        );

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('error', $content['status']);
        $this->assertEquals('Payment gateway "unknown_gateway" not found.', $content['error']['message']);
        $this->assertEquals(404, $content['error']['code']);
    }

    public function testHandleRequestWithValidInputUsingAciGateway(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/app/example/aci',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'amount' => 50.0,
                'currency' => 'EUR',
                'cardNumber' => '4200000000000000',
                'cardExpMonth' => 12,
                'cardExpYear' => 2025,
                'cardCvv' => '123',
            ])
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals('success', $content['status']);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('transactionId', $content['data']);
        $this->assertArrayHasKey('createdAt', $content['data']);
        $this->assertEquals(50.0, $content['data']['amount']);
        $this->assertEquals('EUR', $content['data']['currency']);
    }
}
