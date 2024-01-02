<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TangentApiClient;

it('throws an exception if required config is not correct', function ($config) {
    expect(fn () => new TangentApiClient($config))
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

it('can send sales hourly data', function () {
    Http::fake([
        'example.com/*' => Http::response([
            'access_token' => 'valid_token',
            'expires_in' => 30 * 60,
        ]),
    ]);

    $client = new TangentApiClient([
        'base_uri' => 'http://example.com',
        'grant_type' => 'client_credentials',
        'username' => 'test_user',
        'password' => 'test_password',
    ]);

    $response = $client->sendSalesHourly([
        // todo: add test data
    ]);

    Http::assertSent(function ($request) {
        return $request->url() == 'http://example.com/SalesHourly';
    });

    expect($response->ok())->toBeTrue();
});

it('returns a valid token from cache', function () {
    Cache::shouldReceive('get')
        ->with('trx-mall-upload-sales-data-api-token')
        ->andReturn('cached_token');

    $client = new TangentApiClient([
        'base_uri' => 'http://example.com',
        'grant_type' => 'client_credentials',
        'username' => 'test_user',
        'password' => 'test_password',
    ]);

    $method = new ReflectionMethod(TangentApiClient::class, 'getToken');

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

    $client = new TangentApiClient([
        'base_uri' => 'http://example.com',
        'grant_type' => 'client_credentials',
        'username' => 'test_user',
        'password' => 'test_password',
    ]);

    $method = new ReflectionMethod(TangentApiClient::class, 'getToken');

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

    $client = new TangentApiClient([
        'base_uri' => 'http://example.com',
        'grant_type' => 'client_credentials',
        'username' => 'test_user',
        'password' => 'test_password',
    ]);

    expect(function () use ($client) {
        $method = new ReflectionMethod(TangentApiClient::class, 'getToken');

        $method->setAccessible(true);

        $method->invoke($client);
    })->toThrow(\Exception::class, 'Token retrieval failed');
});

it('returns the correct expiry prior to token', function () {

    $client = new TangentApiClient([
        'base_uri' => 'http://example.com',
        'grant_type' => 'client_credentials',
        'username' => 'test_user',
        'password' => 'test_password',
    ]);

    $expiry = 1800; // 30 minutes
    $min = 5;

    $expectedExpiryPriorToToken = time() + $expiry - 60 * $min;

    $method = new ReflectionMethod(TangentApiClient::class, 'getExpiryPriorToToken');

    $method->setAccessible(true);

    $actualExpiryPriorToToken = $method->invoke($client, $expiry, $min);

    expect($actualExpiryPriorToToken)->toBe($expectedExpiryPriorToToken);
});
