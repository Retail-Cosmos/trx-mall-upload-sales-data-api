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
                'machine_id' => 853495394, // Machine ID as provided by the mall per store.
                'store_identifier' => 'store1', // This is the store identifier that will be used in the sales data
                'gst_registered' => true, // Boolean value to indicate whether the store is GST registered.
            ],
            // more stores as needed...
        ];
    }

    /**
     * @return Collection<int,mixed>
     */
    public function getSales(string $date, string $storeIdentifier): Collection
    {
        return collect([
            [
                'happened_at' => '2024-01-01 00:00:00', // Date and time of the sale in the format `Y-m-d H:i:s`.
                'net_amount' => 191.54,
                'gst' => 1.55,
                'discount' => 0,
                'payments' => [ // the amount of each payment type after discount and before GST
                    PaymentType::CASH() => 8.97,
                    PaymentType::TNG() => 0,
                    PaymentType::VISA() => 76.78,
                    PaymentType::MASTERCARD() => 0,
                    PaymentType::AMEX() => 47.80,
                    PaymentType::VOUCHER() => 0,
                    PaymentType::OTHERS() => 57.99,
                ],
            ],
            // More sales...
        ]);
    }
}
