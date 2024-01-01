<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as LogFacade;
use RetailCosmos\TrxMallUploadSalesDataApi\Support\Log;

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

    Log::info(message: $message, context: $context);
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

    Log::error(message: $message, context: $context);
});

it('can log info message with command', function () {
    $message = 'This is an info message';
    $context = ['key' => 'value'];

    $command = mock(Command::class);
    $command->shouldReceive('info')
        ->with($message)
        ->once();

    LogFacade::shouldReceive('channel')
        ->with('stack')
        ->andReturnSelf();

    LogFacade::shouldReceive('info')
        ->with($message, $context)
        ->once();

    config(['trx-mall-upload-sales-data-api.log.channel' => 'stack']);

    Log::info($message, $context, $command);
});

it('can log error message with command', function () {
    $message = 'This is an error message';
    $context = ['key' => 'value'];

    $command = mock(Command::class);
    $command->shouldReceive('error')
        ->with($message)
        ->once();

    LogFacade::shouldReceive('channel')
        ->with('stack')
        ->andReturnSelf();

    LogFacade::shouldReceive('error')
        ->with($message, $context)
        ->once();

    config(['trx-mall-upload-sales-data-api.log.channel' => 'stack']);

    Log::error($message, $context, $command);
});
