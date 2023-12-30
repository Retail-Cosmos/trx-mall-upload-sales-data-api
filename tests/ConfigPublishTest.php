<?php

use Illuminate\Support\Facades\Artisan;

it('can publish config file', function () {

    Artisan::call('vendor:publish', ['--tag' => 'trx-mall-upload-sales-data-api-config']);

    $configPath = config_path('trx-mall-upload-sales-data-api.php');

    expect(file_exists($configPath))->toBeTrue();
});

