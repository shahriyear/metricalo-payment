<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientService
{

    public function __construct(private HttpClientInterface $httpClient) {}

    public function post(string $url, array $data, array $headers = []): array
    {
        $logs = [
            'url' => $url,
            'payload' => $data,
            'headers' => $headers,
            'curlInfo' => [],
        ];
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => $headers,
                'body' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(false);

            $status = $statusCode >= 200 && $statusCode < 300;

            $logs['curlInfo'] = $response->getInfo();

            if (!$status) {
                return [
                    'status' => 'error',
                    'code' => $statusCode,
                    'errors' => $content,
                    'logs' => $logs
                ];
            }

            return [
                'status' => 'success',
                'code' => $statusCode,
                'data' => $content,
                'logs' => $logs
            ];

            return $response;
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'code' => $statusCode,
                'errors' => [
                    'message' => $e->getMessage()
                ],
                'logs' => $logs
            ];
        }
    }
}
