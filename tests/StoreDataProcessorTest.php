<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Services\StoreDataProcessor;

it('validates and prepare the store data', function () {
    $storeData = [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => '456',
            'store_identifier' => 'store2',
            'gst_registered' => false,
        ],
    ];

    $processor = new StoreDataProcessor();

    $processedData = $processor->process($storeData);

    expect($processedData[0]['gstregistered'])->toBe('Y');
    expect($processedData[1]['gstregistered'])->toBe('N');
});

it('throws exception for invalid store data', function () {
    $storeData = [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => '123', // Duplicate machine_id
            'store_identifier' => 'store2',
            'gst_registered' => false,
        ],
    ];

    $processor = new StoreDataProcessor();

    expect(function () use ($processor, $storeData) {
        $processor->process($storeData);
    })->toThrow(\Exception::class);
});
