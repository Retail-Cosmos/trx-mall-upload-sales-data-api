<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TrxApiClient;

it('throws an exception if required config is not correct', function ($config) {
    expect(fn () => new TrxApiClient($config))
        ->toThrow(\Exception::class);
})->with([
    'empty config' => [
        'config' => [

        ]],
    'missing key' => [
        'config' => [
            'grant_type' => 'client_credentials',
            'username' => 'test_user',
            'password' => 'test_password',
        ],
    ],
    'invalid_uri' => [
        'config' => [
            'base_uri' => 'invalid_uri',
            'grant_type' => 'client_credentials',
            'username' => 'test_user',
            'password' => 'test_password',
        ],
    ],
    'has_array' => [
        'config' => [
            'base_uri' => 'http://example.com',
            'grant_type' => 'client_credentials',
            'username' => [],
            'password' => 'test_password',
        ],
    ],
]);

describe('with valid config', function () {

    beforeEach(function () {
        $this->config = [
            'base_uri' => 'http://example.com',
            'grant_type' => 'client_credentials',
            'username' => 'test_user',
            'password' => 'test_password',
        ];
    });

    it('can send sales hourly data', function () {
        Http::fake([
            'example.com/*' => Http::response([
                'access_token' => 'valid_token',
                'expires_in' => 30 * 60,
            ]),
        ]);

        $client = new TrxApiClient($this->config);

        $response = $client->sendSalesHourly([
            [
                'store_id' => 'test_store_id',
                'date' => '2024-01-01',
                'hour' => '00',
                'payment_type' => 'cash',
                'amount' => '100.00',
                'quantity' => '1',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request->url() == 'http://example.com/SalesHourly';
        });

        expect($response->ok())->toBeTrue();
    });

    it('returns a valid token from cache', function () {
        Http::fake();

        Cache::shouldReceive('get')
            ->with('trx-mall-upload-sales-data-api-token')
            ->andReturn('cached_token');

        $client = new TrxApiClient($this->config);

        $method = new ReflectionMethod(TrxApiClient::class, 'getToken');

        $method->setAccessible(true);

        $token = $method->invoke($client);

        expect($token)->toBe('cached_token');
    });

    it('fetches a new token and caches it', function () {
        Cache::shouldReceive('get')
            ->with('trx-mall-upload-sales-data-api-token')
            ->andReturn(null);

        Http::fake([
            'example.com/*' => Http::response([
                'access_token' => 'new_token',
                'expires_in' => 1800,
            ]),
        ]);

        Cache::shouldReceive('put')
            ->with('trx-mall-upload-sales-data-api-token', 'new_token', \Mockery::type('int'))
            ->once();

        $client = new TrxApiClient($this->config);

        $method = new ReflectionMethod(TrxApiClient::class, 'getToken');

        $method->setAccessible(true);

        $token = $method->invoke($client);

        expect($token)->toBe('new_token');
    });

    it('throws an exception if token retrieval fails', function () {
        Cache::shouldReceive('get')
            ->with('trx-mall-upload-sales-data-api-token')
            ->andReturn(null);

        Http::fake([
            'example.com/*' => Http::response([
                'error_description' => 'Token retrieval failed',
            ], 500),
        ]);

        $client = new TrxApiClient($this->config);

        expect(function () use ($client) {
            $method = new ReflectionMethod(TrxApiClient::class, 'getToken');

            $method->setAccessible(true);

            $method->invoke($client);
        })->toThrow(\Exception::class, 'Token retrieval failed');
    });
});
