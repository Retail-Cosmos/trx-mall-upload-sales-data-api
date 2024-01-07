<?php

use App\Services\TrxMallUploadSalesDataApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Psr\Log\LoggerInterface;
use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

beforeEach(function () {
    $this->trxLogChannel = mock(LoggerInterface::class);

    $this->serviceMock = mock(TrxMallUploadSalesDataApiService::class);
    $this->app->instance(TrxMallUploadSalesDataApiService::class, $this->serviceMock);

    config([
        'trx_mall_upload_sales_data_api.api' => [
            'base_uri' => 'https://example.com',
            'grant_type' => 'password',
            'username' => 'username',
            'password' => 'password',
        ],
        'trx_mall_upload_sales_data_api.date_of_first_sales_upload' => '2024-01-01',
        'trx_mall_upload_sales_data_api.log.channel' => 'stack',
    ]);

    Notification::fake();
    Http::fake([
        'example.com/*' => Http::response([
            'access_token' => 'new_token',
            'expires_in' => 1800,
        ]),
    ]);
});

it('fails when date is invalid', function () {
    $this->artisan('tangent:send-sales', [
        'date' => 'invalid-date',
    ])->assertExitCode(1);
});

it('fails when config is invalid', function () {
    config([
        'trx_mall_upload_sales_data_api.api' => null,
    ]);

    $this->artisan('tangent:send-sales')->assertExitCode(1);
});

it('sends sales data to tangent api', function () {

    $this->serviceMock->shouldReceive('getStores')->once()
        ->andReturn([
            [
                'machine_id' => 123,
                'store_identifier' => 'store1',
                'gst_registered' => true,
            ],
        ]);

    $this->serviceMock->shouldReceive('getSales')->once()
        ->andReturn(collect([
            [
                'happened_at' => '2024-01-01 00:00:00',
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
                'happened_at' => '2024-01-01 00:00:00',
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
        ]));

    $this->artisan('tangent:send-sales', [
        'date' => '2024-01-01',
    ])->assertExitCode(0);
});
