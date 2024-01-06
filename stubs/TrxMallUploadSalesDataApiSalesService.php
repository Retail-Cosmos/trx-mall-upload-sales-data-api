<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RetailCosmos\TrxMallUploadSalesDataApi\Contracts\TrxSalesService;

class TrxMallUploadSalesDataApiSalesService implements TrxSalesService
{
    public function getStoreData(?string $storeIdentifier = null): array
    {
        return [];
    }

    public function getSalesData(string $date, string $storeIdentifier): Collection
    {
        return collect();
    }
}
