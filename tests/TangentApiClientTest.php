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
            'expires_in' => 3600,
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
