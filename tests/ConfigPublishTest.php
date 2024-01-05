<?php

use Illuminate\Support\Facades\Artisan;

it('can publish config file', function () {

    Artisan::call('vendor:publish', ['--tag' => 'trx-mall-upload-sales-data-api-config']);

    $configPath = config_path('trx-mall-upload-sales-data-api.php');

    expect(file_exists($configPath))->toBeTrue();
});

it('has all required configuration available', function () {

    $config = config('trx-mall-upload-sales-data-api');

    expect($config)->toBeArray();

    // check if all required keys are present
    expect(array_keys($config))->toEqual([
        'api',
        'log',
        'date_of_first_sales'
    ]);

    // check if all required sub-keys are present
    expect(array_keys($config['api']))->toEqual([
        'base_uri',
        'grant_type',
        'username',
        'password',
    ]);
    expect(array_keys($config['log']))->toEqual([
        'channel',
    ]);
});
