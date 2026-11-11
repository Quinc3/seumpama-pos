<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AnnServiceClient
{
    public function health(): array
    {
        return $this->request()->get('/api/v1/health')->json();
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
}
