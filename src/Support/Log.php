<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Support;

use Illuminate\Support\Facades\Log as LogFacade;

class Log
{
    public static function info(string $message, array $context = []): void
    {
        LogFacade::channel(config('trx-mall-upload-sales-data-api.log.channel'))
            ->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        LogFacade::channel(config('trx-mall-upload-sales-data-api.log.channel'))
            ->error($message, $context);
    }
}
