<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLServiceClient
{
    protected string $baseUrl;

    protected int $timeout;

    protected array $endpoints;

    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('ml.url');
        $this->timeout = config('ml.timeout', 30);
        $this->endpoints = config('ml.endpoints', []);
        $this->apiKey = config('ml.api_key');
    }

    /**
     * Create a configured HTTP client.
     */
    protected function client(): PendingRequest
    {
        $client = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry(
                config('ml.retry.times', 3),
                config('ml.retry.sleep', 100)
            )
            ->acceptJson()
            ->asJson();

        if ($this->apiKey) {
            $client->withHeaders([
                'X-API-Key' => $this->apiKey,
            ]);
        }

        return $client;
    }

    /**
     * Predict employee turnover risk.
     */
    public function predictTurnover(array $data): array
    {
        return $this->post(
            $this->endpoints['predict']['turnover'] ?? '/api/v1/predict/turnover',
            $data
        );
    }

    /**
     * Detect attendance anomalies.
     */
    public function detectAnomalies(array $data): array
    {
        return $this->post(
            $this->endpoints['analyze']['anomaly_detection'] ?? '/api/v1/analyze/anomalies',
            $data
        );
    }

    /**
     * Predict leave patterns.
     */
    public function predictLeave(array $data): array
    {
        return $this->post(
            $this->endpoints['predict']['leave'] ?? '/api/v1/predict/leave',
            $data
        );
    }

    /**
     * Query NLP for HR data insights.
     */
    public function queryNLP(array $data): array
    {
        return $this->post(
            $this->endpoints['chat']['query'] ?? '/api/v1/chat/query',
            $data
        );
    }

    /**
     * Query policy chatbot.
     */
    public function chatPolicy(array $data): array
    {
        return $this->post(
            $this->endpoints['chat']['policy'] ?? '/api/v1/chat/policy',
            $data
        );
    }

    /**
     * Make a POST request to the ML service.
     */
    protected function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->client()->post($endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ML Service request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(
                "ML Service returned status {$response->status()}: {$response->body()}"
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('ML Service connection failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'ML Service is unreachable: '.$e->getMessage()
            );
        }
    }

    /**
     * Make a GET request to the ML service.
     */
    protected function get(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->client()->get($endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException(
                "ML Service returned status {$response->status()}: {$response->body()}"
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \RuntimeException(
                'ML Service is unreachable: '.$e->getMessage()
            );
        }
    }

    /**
     * Check if the ML service is healthy.
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->client()->get('/health');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
