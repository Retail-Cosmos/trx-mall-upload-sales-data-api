<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RetailCosmos\TrxMallUploadSalesDataApi\Contracts\TrxSalesService;

class TrxMallUploadSalesDataApiService implements TrxSalesService
{
    public function getStores(?string $storeIdentifier = null): array
    {
        return [];
    }

    public function getSales(string $date, string $storeIdentifier): Collection
    {
        return collect();
    }
}
