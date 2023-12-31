<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Services\SalesDataProcessor;

todo('transforms the sales data', function () {
    $sales = [
        // todo: sample sales data here
    ];

    $processor = new SalesDataProcessor();
    $processor->process($sales);

    // todo: assertions to test the transformation of sales data
});

todo('validates the sales data', function () {
    $sales = [
        // todo: sample sales data here
    ];

    $processor = new SalesDataProcessor();

    expect(function () use ($processor, $sales) {
        $processor->process($sales);
    })->not->toThrow();
});

todo('throws an exception for invalid sales data', function () {
    $sales = [
        // todo: sample invalid sales data here
    ];

    $processor = new SalesDataProcessor();

    expect(function () use ($processor, $sales) {
        $processor->process($sales);
    })->toThrow(\Exception::class);
});
