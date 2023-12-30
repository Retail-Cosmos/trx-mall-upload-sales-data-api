<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Clients;

use Illuminate\Http\Client\Response;
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

    private function validateConfig(array $keys): void
    {
        foreach ($keys as $key) {
            if (! isset($this->config[$key])) {
                throw new \Exception("$key is not set");
            }
        }
    }

    private function getToken(): string
    {
        if (session('token_expiry') > time()) {
            return session('token');
        }
        $response = Http::asJson()
            ->acceptJson()
            ->get($this->config['base_uri'].'/token', [
                'grant_type' => $this->config['grant_type'],
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ]);

        if ($response->ok()) {
            session(['token' => $response->json('access_token')]);
            session(['token_expiry' => time() + $response->json('expires_in')]);

            return $response->json('access_token');
        } else {
            new \Exception($response->json('error_description'));
        }
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
}
