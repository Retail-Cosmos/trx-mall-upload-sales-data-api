<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi;

use App\Services\TrxMallUploadSalesDataApiSalesService;
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

        $this->app->singleton(
            TrxSalesService::class,
            TrxMallUploadSalesDataApiSalesService::class,
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
                __DIR__.'/../stubs/TrxMallUploadSalesDataApiSalesService.php' => app_path('Services/TrxMallUploadSalesDataApiSalesService.php'),
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
