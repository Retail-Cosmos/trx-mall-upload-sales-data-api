<?php

use App\Services\TrxMallUploadSalesDataApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Psr\Log\LoggerInterface;
use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;
use RetailCosmos\TrxMallUploadSalesDataApi\Notifications\TrxApiStatusNotification;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->trxLogChannel = Mockery::mock(LoggerInterface::class);

    $this->serviceMock = Mockery::mock(TrxMallUploadSalesDataApiService::class);
    $this->app->instance(TrxMallUploadSalesDataApiService::class, $this->serviceMock);

    config([
        'trx_mall_upload_sales_data_api.api' => [
            'base_uri' => 'https://example.com',
            'grant_type' => 'password',
            'username' => 'username',
            'password' => 'password',
        ],
        'trx_mall_upload_sales_data_api.date_of_first_sales_upload' => '2024-01-01',
    ]);

    Notification::fake();
    Http::fake([
        'example.com/*' => Http::response([
            'access_token' => 'new_token',
            'expires_in' => 1800,
        ]),
    ]);
});

describe('failure cases without notification', function () {

    it('fails when date is invalid', function () {
        artisan('trx:send-sales', [
            '--date' => 'invalid-date',
        ])->assertExitCode(1);
    });

    it('fails when config is invalid', function ($invalidConfig) {
        config($invalidConfig);

        artisan('trx:send-sales')->assertExitCode(1);
    })->with([
        'missing_base_uri' => [
            'trx_mall_upload_sales_data_api.api' => [
                'grant_type' => 'password',
                'username' => 'username',
                'password' => 'password',
            ],
        ],
        'missing_date_of_first_sales_upload' => [
            'trx_mall_upload_sales_data_api.date_of_first_sales_upload' => null,
        ],
        'invalid_base_uri' => [
            'trx_mall_upload_sales_data_api.api' => [
                'base_uri' => 'example.com',
                'username' => 'username',
                'password' => 'password',
            ],
        ],
        'invalid_date_of_first_sales_upload' => [
            'trx_mall_upload_sales_data_api.date_of_first_sales_upload' => 'invalid-date',
        ],
    ]);

    it('fails when no stores found', function () {
        $this->serviceMock->shouldReceive('getStores')->once()
            ->andReturn([]);

        artisan('trx:send-sales', [
            '--date' => '2024-01-11',
        ])->assertExitCode(1);
    });

    it('sends nothing when no sales found', function () {
        $this->serviceMock->shouldReceive('getStores')->once()
            ->andReturn([[
                'machine_id' => 123,
                'store_identifier' => 'store1',
                'gst_registered' => true,
            ]]);

        $this->serviceMock->shouldReceive('getSales')->once()
            ->andReturn(collect([]));

        artisan('trx:send-sales', [
            '--date' => '2024-01-11',
        ])->assertExitCode(0);
    });

    afterEach(function () {
        Notification::assertNothingSent();
        Http::assertNothingSent();
    });

});

describe('failure cases with notification', function () {
    beforeEach(function () {
        $this->mailConfig = ['name' => 'test', 'email' => 'user@example.com'];
        config([
            'trx_mall_upload_sales_data_api.notifications.mail.name' => $this->mailConfig['name'],
            'trx_mall_upload_sales_data_api.notifications.mail.email' => $this->mailConfig['email'],
        ]);
    });

    it('sends failure notification when no stores found', function () {

        $this->serviceMock->shouldReceive('getStores')->once()
            ->andReturn([]);

        artisan('trx:send-sales', [
            '--date' => '2024-01-11',
        ])->assertExitCode(1);
    });

    afterEach(function () {
        Notification::assertSentOnDemand(
            TrxApiStatusNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] == $this->mailConfig['email']
                && $notification->status === 'error'
                && $notification->name === $this->mailConfig['name'];
            }
        );
        Http::assertNothingSent();
    });
});

describe('success cases without notification', function () {
    it('sends sales data to TRX api', function (array $stores, array $sales) {
        $this->serviceMock->shouldReceive('getStores')->once()
            ->andReturn($stores);

        $this->serviceMock->shouldReceive('getSales')->twice()
            ->andReturn(collect($sales));

        artisan('trx:send-sales', [
            '--date' => '2024-01-11',
        ])->assertExitCode(0);

    })->with('valid_data');

    afterEach(function () {
        Notification::assertNothingSent();
        Http::assertSentCount(3); // 1 for fetching token, 2 for sending sales for 2 stores
    });
});

describe('success cases with notification', function () {
    beforeEach(function () {
        $this->mailConfig = ['name' => 'test', 'email' => 'user@example.com'];
        config([
            'trx_mall_upload_sales_data_api.notifications.mail.name' => $this->mailConfig['name'],
            'trx_mall_upload_sales_data_api.notifications.mail.email' => $this->mailConfig['email'],
        ]);
    });

    it('sends success notification', function (array $stores, array $sales) {
        $this->serviceMock->shouldReceive('getStores')->once()
            ->andReturn($stores);

        $this->serviceMock->shouldReceive('getSales')->twice()
            ->andReturn(collect($sales));

        artisan('trx:send-sales', [
            '--date' => '2024-01-11',
        ])->assertExitCode(0);
    })->with('valid_data');

    afterEach(function () {
        Notification::assertSentOnDemand(
            TrxApiStatusNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] == $this->mailConfig['email']
                && $notification->status === 'success'
                && $notification->name === $this->mailConfig['name'];
            }
        );
        Http::assertSentCount(3); // 1 for fetching token, 2 for sending sales for 2 stores
    });
});

dataset('valid_data', [[
    [
        [
            'machine_id' => 123,
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => 124,
            'store_identifier' => 'store2',
            'gst_registered' => false,
        ],
    ],
    [
        [
            'happened_at' => '2024-01-11 00:00:00',
            'net_amount' => 191.54,
            'gst' => 1.55,
            'discount' => 0,
            'payments' => [
                PaymentType::CASH() => 8.97,
                PaymentType::TNG() => 0,
                PaymentType::VISA() => 76.78,
                PaymentType::MASTERCARD() => 0,
                PaymentType::AMEX() => 47.80,
                PaymentType::VOUCHER() => 0,
                PaymentType::OTHERS() => 57.99,
            ],
        ], [
            'happened_at' => '2024-01-11 00:00:00',
            'net_amount' => 391.54,
            'gst' => 12.65,
            'discount' => 10,
            'payments' => [
                PaymentType::CASH() => 18.97,
                PaymentType::TNG() => 0,
                PaymentType::VISA() => 176.78,
                PaymentType::MASTERCARD() => 0,
                PaymentType::AMEX() => 47.80,
                PaymentType::VOUCHER() => 0,
                PaymentType::OTHERS() => 0,
            ],
        ],
        [
            'happened_at' => '2024-01-11 00:00:00',
            'net_amount' => 191.54,
            'gst' => 1.55,
            'discount' => 0,
            'payments' => [
                PaymentType::CASH() => 8.97,
                PaymentType::TNG() => 0,
                PaymentType::VISA() => 76.78,
                PaymentType::MASTERCARD() => 0,
                PaymentType::AMEX() => 47.80,
                PaymentType::VOUCHER() => 0,
                PaymentType::OTHERS() => 57.99,
            ],
        ], [
            'happened_at' => '2024-01-11 00:00:00',
            'net_amount' => 391.54,
            'gst' => 12.65,
            'discount' => 10,
            'payments' => [
                PaymentType::CASH() => 18.97,
                PaymentType::TNG() => 0,
                PaymentType::VISA() => 176.78,
                PaymentType::MASTERCARD() => 0,
                PaymentType::AMEX() => 47.80,
                PaymentType::VOUCHER() => 0,
                PaymentType::OTHERS() => 0,
            ],
        ],
    ],
]]);
