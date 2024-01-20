<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Clients;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrxApiClient
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

    /**
     * @param  array<string, mixed>  $sales
     *
     * @throws \Exception
     */
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

    /**
     * @param  array<int, string>  $keys
     *
     * @throws \Exception
     */
    private function validateConfig(array $keys): void
    {
        foreach ($keys as $key) {
            if (! isset($this->config[$key])) {
                throw new \Exception("$key is not set");
            }
        }

        $validator = validator($this->config, [
            'base_uri' => 'required|url',
            'grant_type' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * @throws \Exception
     */
    private function getToken(): string
    {
        $token = Cache::get('trx-mall-upload-sales-data-api-token');

        if ($token !== null) {
            return $token;
        }

        $response = Http::acceptJson()
            ->withBody(Arr::query([
                'grant_type' => $this->config['grant_type'],
                'username' => $this->config['username'],
                'password' => $this->config['password'],
            ]), 'text/plain')
            ->get($this->config['base_uri'].'/token');

        if ($response->ok()) {
            $token = $response->json('access_token');

            Cache::put(
                'trx-mall-upload-sales-data-api-token',
                $token,
                $this->getExpiryPriorToToken($response->json('expires_in'), minutes: 5)
            );

            return (string) $token;
        } else {
            throw new \Exception($response->json('error_description'));
        }
    }

    /**
     * if token expires in $expiry seconds,
     * we will get a new token $minutes before it expires
     */
    private function getExpiryPriorToToken(int $expiry, int $minutes): int
    {
        return time() + $expiry - 60 * $minutes;
    }
}
