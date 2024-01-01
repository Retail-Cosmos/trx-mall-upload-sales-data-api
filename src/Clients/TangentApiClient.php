<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Clients;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TangentApiClient
{
    public function __construct(private array $config)
    {
        $this->validateConfig([
            'base_uri',
            'grant_type',
            'username',
            'password',
        ]);

    }

    public function sendSalesHourly(array $sales): Response
    {
        $token = $this->getToken();

        $response = Http::asJson()
            ->acceptJson()
            ->withToken($token)
            ->post($this->config['base_uri'].'/SalesHourly', [
                'sales' => $sales,
            ]);

        return $response;
    }

    private function validateConfig(array $keys): void
    {
        foreach ($keys as $key) {
            if (! isset($this->config[$key])) {
                throw new \Exception("$key is not set");
            }
        }

        if (validator($this->config, [
            'base_uri' => 'required|url',
            'grant_type' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ])->fails()) {
            throw new \Exception('Invalid config');
        }
    }

    private function getToken(): string
    {
        $token = Cache::get('trx-mall-upload-sales-data-api-token');

        if ($token !== null) {
            return $token;
        }

        $response = Http::asJson()
            ->acceptJson()
            ->get($this->config['base_uri'].'/token', [
                'grant_type' => $this->config['grant_type'],
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ]);

        if ($response->ok()) {
            $token = $response->json('access_token');
            $expiry = time() + $response->json('expires_in') - 60 * 5;

            Cache::put('trx-mall-upload-sales-data-api-token', $token, $expiry);

            return $token;
        } else {
            throw new \Exception($response->json('error_description'));
        }
    }
}
