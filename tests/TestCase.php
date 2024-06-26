<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use RetailCosmos\TrxMallUploadSalesDataApi\TrxMallUploadSalesDataApiServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'RetailCosmos\\TrxMallUploadSalesDataApi\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            TrxMallUploadSalesDataApiServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_trx-mall-upload-sales-data-api_table.php.stub';
        $migration->up();
        */
    }

    public function assertNotificationDetails(
        $notification,
        $notifiable,
        string $expectedEmail,
        string $expectedStatus,
        string $expectedReceiverName
    ): bool {
        $routeKeys = array_keys($notifiable->routes['mail']);
        if (count($routeKeys) > 0) {
            $email = $routeKeys[0];

            return $email === $expectedEmail
                && $notification->status === $expectedStatus
                && $notification->name === $expectedReceiverName;
        }

        return false;
    }
}
