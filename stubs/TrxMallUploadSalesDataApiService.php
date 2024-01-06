<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RetailCosmos\TrxMallUploadSalesDataApi\Contracts\TrxSalesService;
use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

class TrxMallUploadSalesDataApiService implements TrxSalesService
{
    /**
     * @return array<int,mixed>
     */
    public function getStores(?string $storeIdentifier = null): array
    {
        return [
            [
                'machine_id' => 123,
                'store_identifier' => 'store1',
                'gst_registered' => true,
            ],
            [
                'machine_id' => 456,
                'store_identifier' => 'store2',
                'gst_registered' => false,
            ],
        ];
    }

    /**
     * @return Collection<int,mixed>
     */
    public function getSales(string $date, string $storeIdentifier): Collection
    {
        return collect([
            [
                'happened_at' => '2023-01-01 00:00:00',
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
                'happened_at' => '2023-01-01 00:00:00',
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
    }
}
