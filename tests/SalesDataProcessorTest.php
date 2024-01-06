<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;
use RetailCosmos\TrxMallUploadSalesDataApi\Services\SalesDataProcessor;

it('transforms the sales data', function () {
    $sales = collect([
        [
            'happened_at' => '2024-02-01 00:00:00',
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
            'happened_at' => '2024-02-01 00:00:00',
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
    ]);
    $storeData = [
        'machineid' => '123',
        'gstregistered' => 'Y',
    ];

    $processor = new SalesDataProcessor('2024-02-01');
    $result = $processor->process($sales, $storeData);

    expect(count($result))->toBe(24);

    $sale = collect($result)->where('sale.hour', 0)->first();

    expect($sale['sale'])->toBe([
        'machineid' => '123',
        'batchid' => 1,
        'date' => '20240201',
        'hour' => 0,
        'receiptcount' => 2,
        'gto' => 583.08,
        'gst' => 14.20,
        'discount' => 10.00,
        PaymentType::CASH() => 27.94,
        PaymentType::TNG() => 0.00,
        PaymentType::VISA() => 253.56,
        PaymentType::MASTERCARD() => 0.00,
        PaymentType::AMEX() => 95.60,
        PaymentType::VOUCHER() => 0.00,
        PaymentType::OTHERS() => 57.99,
        'gstregistered' => 'Y',
    ]);
});

it('throws exception when sales data is invalid', function ($sales) {
    $sales = collect($sales);
    $storeData = [
        'machineid' => '123',
        'gstregistered' => 'Y',
    ];

    $processor = new SalesDataProcessor('2024-02-01');
    $processor->process($sales, $storeData);
})
->with([
    'invalid date' => [
        [
            [
                'happened_at' => '2024-02-02 00:00:00',
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
            ],
        ],
    ],
    'one of key is missing' => [
        [
            [
                'happened_at' => '2024-02-01 00:00:00',
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
                'happened_at' => '2024-02-01 00:00:00',
                'net_amount' => 391.54,
                'gst' => 12.65,
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
    ],
])
->throws(\Exception::class);
