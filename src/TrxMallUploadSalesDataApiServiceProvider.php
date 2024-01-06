<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi;

use RetailCosmos\TrxMallUploadSalesDataApi\Commands\SendSalesCommand;
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
            ->hasCommand(SendSalesCommand::class);
    }
}
