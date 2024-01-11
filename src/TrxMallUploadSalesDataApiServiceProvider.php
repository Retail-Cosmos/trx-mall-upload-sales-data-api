<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi;

use App\Services\TrxMallUploadSalesDataApiService;
use RetailCosmos\TrxMallUploadSalesDataApi\Commands\SendSalesCommand;
use RetailCosmos\TrxMallUploadSalesDataApi\Contracts\TrxSalesService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TrxMallUploadSalesDataApiServiceProvider extends PackageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->bind(
            TrxSalesService::class,
            TrxMallUploadSalesDataApiService::class,
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/trx-mall-upload-sales-data-api.php', 'trx_mall_upload_sales_data_api'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../stubs/TrxMallUploadSalesDataApiService.php' => app_path('Services/TrxMallUploadSalesDataApiService.php'),
            ], 'trx-mall-upload-sales-data-api-service');
        }
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('trx-mall-upload-sales-data-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(SendSalesCommand::class);
    }
}
