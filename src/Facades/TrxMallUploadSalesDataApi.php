<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RetailCosmos\TrxMallUploadSalesDataApi\TrxMallUploadSalesDataApi
 */
class TrxMallUploadSalesDataApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RetailCosmos\TrxMallUploadSalesDataApi\TrxMallUploadSalesDataApi::class;
    }
}
