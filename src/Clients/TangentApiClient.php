<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class TangentApiClient
{
    public function __construct(private array $config)
    {
        // todo: check if we have required configs
    }

    private function getToken(): string
    {
        if (session('token_expiry') > time()) {
            return session('token');
        }
        $response = Http::get($this->config['base_uri'].'/token', [
            'grant_type' => $this->config['grant_type'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
        ]);

        if ($response->ok()) {
            $response = $response->json();
            session(['token' => $response['access_token']]);
            session(['token_expiry' => time() + $response['expires_in']]);

            return $response['access_token'];
        } else {
            new \Exception($response->json()['error_description']);
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
