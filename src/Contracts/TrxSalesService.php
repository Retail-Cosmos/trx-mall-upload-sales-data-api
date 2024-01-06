<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Contracts;

use Illuminate\Support\Collection;

interface TrxSalesService
{
    public function getStores(?string $storeIdentifier = null): array;

    public function getSales(string $date, string $storeIdentifier): Collection;
}
