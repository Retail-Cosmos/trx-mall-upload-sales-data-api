<?php

use Illuminate\Support\Facades\Http;
use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TangentApiClient;

it('throws an exception if required config keys are not set', function () {
    expect(function () {
        new TangentApiClient([]);
    })->toThrow(\Exception::class);
});

it('gets a valid token', function () {
    Http::fake([
        'example.com/*' => Http::response([
            'access_token' => 'valid_token',
            'expires_in' => 30*60,
        ]),
    ]);

    $client = new TangentApiClient([
        'base_uri' => 'http://example.com',
        'grant_type' => 'client_credentials',
        'username' => 'test_user',
        'password' => 'test_password',
    ]);

    $method = new ReflectionMethod(TangentApiClient::class, 'getToken');

    $method->setAccessible(true);

    $token = $method->invoke($client);

    expect($token)->toBe('valid_token');
    expect(session('token'))->toBe('valid_token');
    expect(session('token_expiry'))->toBeGreaterThan(time());
});

it('can send sales hourly data', function () {
    Http::fake([
        'example.com/*' => Http::response([
            'access_token' => 'valid_token',
            'expires_in' => 30*60,
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
