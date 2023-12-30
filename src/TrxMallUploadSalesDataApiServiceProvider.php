<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use RetailCosmos\TrxMallUploadSalesDataApi\Commands\TrxMallUploadSalesDataApiCommand;

class TrxMallUploadSalesDataApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('trx-mall-upload-sales-data-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_trx-mall-upload-sales-data-api_table')
            ->hasCommand(TrxMallUploadSalesDataApiCommand::class);
    }
}
