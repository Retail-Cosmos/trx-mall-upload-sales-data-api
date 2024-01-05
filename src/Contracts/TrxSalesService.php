<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Contracts;

use Illuminate\Support\Collection;

interface TrxSalesService
{
    public function getStoreData(string $storeIdentifier): array;

    public function getSalesData(string $date, string $storeIdentifier): Collection;
}
