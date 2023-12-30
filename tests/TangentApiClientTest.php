<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TangentApiClient;

it('throws an exception if required config keys are not set', function () {
    expect(function () {
        new TangentApiClient([]);
    })->toThrow(\Exception::class);
});
