<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Support\Log;
use Illuminate\Support\Facades\Log as LogFacade;

it('can log info message', function () {
    $message = 'This is an info message';
    $context = ['key' => 'value'];

    LogFacade::shouldReceive('channel')
        ->with('stack')
        ->andReturnSelf();

    LogFacade::shouldReceive('info')
        ->with($message, $context)
        ->once();

    config(['trx-mall-upload-sales-data-api.log.channel' => 'stack']);

    Log::info($message, $context);
});

it('can log error message', function () {
    $message = 'This is an error message';
    $context = ['key' => 'value'];

    LogFacade::shouldReceive('channel')
        ->with('stack')
        ->andReturnSelf();

    LogFacade::shouldReceive('error')
        ->with($message, $context)
        ->once();

    config(['trx-mall-upload-sales-data-api.log.channel' => 'stack']);

    Log::error($message, $context);
});
