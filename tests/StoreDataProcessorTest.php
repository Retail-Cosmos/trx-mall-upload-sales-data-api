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

    $processor = new StoreDataProcessor;

    $processedData = $processor->process($storeData);

    expect($processedData)->toBe([[
        'store_identifier' => 'store1',
        'gstregistered' => 'Y',
        'machineid' => '123',
    ], [
        'store_identifier' => 'store2',
        'gstregistered' => 'N',
        'machineid' => '456',
    ]]);
});

it('throws exception for invalid store data', function ($storeData) {
    $processor = new StoreDataProcessor;

    expect(function () use ($processor, $storeData) {
        $processor->process($storeData);
    })->toThrow(\Exception::class);
})->with([
    'duplicate machine id' => [
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
    ],
    'duplicate store identifier' => [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => '456',
            'store_identifier' => 'store1', // Duplicate store_identifier
            'gst_registered' => false,
        ],
    ],
    'missing machine id' => [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'store_identifier' => 'store2',
            'gst_registered' => false,
        ],
    ],
    'missing store identifier' => [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => '456',
            'gst_registered' => false,
        ],
    ],
    'missing gst registered' => [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => '456',
            'store_identifier' => 'store2',
        ],
    ],
    'invalid gst registered' => [
        [
            'machine_id' => '123',
            'store_identifier' => 'store1',
            'gst_registered' => true,
        ],
        [
            'machine_id' => '456',
            'store_identifier' => 'store2',
            'gst_registered' => 'invalid',
        ],
    ],
]);
