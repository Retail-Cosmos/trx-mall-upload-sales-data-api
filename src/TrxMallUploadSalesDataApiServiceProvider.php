<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi;

use RetailCosmos\TrxMallUploadSalesDataApi\Commands\SendSalesDailyCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TrxMallUploadSalesDataApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('trx-mall-upload-sales-data-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_trx-mall-upload-sales-data-api_table')
            ->hasCommand(SendSalesDailyCommand::class);
    }
}
