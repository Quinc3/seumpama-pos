<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AnnServiceClient
{
    public function health(): array
    {
        return $this->request()->get('/api/v1/health')->json();
    }

    public function getModelStatus(): array
    {
        return $this->request()->get('/api/v1/model/status')->json();
    }

    /**
     * Submit training request. Returns 202 body atau throw kalau gagal.
     *
     * @param array $payload Payload sesuai API_CONTRACT §5.5
     * @throws \RuntimeException
     */
    public function submitTraining(array $payload): array
    {
        $response = $this->request()->post('/api/v1/model/train', $payload);
        $this->ensureOk($response, 'submit training');

        return $response->json();
    }

    /**
     * Get training status by ID. Returns body (200) atau throw kalau 404.
     *
     * @throws \RuntimeException
     */
    public function getTrainingStatus(string $trainingId): array
    {
        $response = $this->request()->get("/api/v1/model/train/{$trainingId}");
        $this->ensureOk($response, 'get training status');

        return $response->json();
    }

    /**
     * Submit prediction request. Returns body — status per ingredient bisa
     * 'valid' atau 'failed' (tidak throw kalau hanya ingredient failure).
     *
     * @throws \RuntimeException kalau HTTP error (bukan ingredient-level failure)
     */
    public function predict(array $payload): array
    {
        $response = $this->request()->post('/api/v1/predictions/demand', $payload);
        $this->ensureOk($response, 'predict');

        return $response->json();
    }

    protected function request(): PendingRequest
    {
        $config = config('services.ann');

        return Http::baseUrl($config['url'])
            ->timeout($config['timeout'])
            ->connectTimeout($config['connect_timeout'])
            ->acceptJson()
            ->when($config['token'], fn(PendingRequest $http) => $http->withToken($config['token']));
    }

    protected function ensureOk(Response $response, string $context): void
    {
        if ($response->successful()) {
            return;
        }

        $body = $response->body();
        if (strlen($body) > 300) {
            $body = substr($body, 0, 300) . '…';
        }

        throw new \RuntimeException(
            "ANN service {$context} failed (HTTP {$response->status()}): {$body}"
        );
    }
}
