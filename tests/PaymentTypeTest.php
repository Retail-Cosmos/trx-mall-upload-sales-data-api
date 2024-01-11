<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

it('returns enum value by calling static method', function () {
    $paymentType = PaymentType::CASH();

    expect($paymentType)->toBe('cash');
});

it('throws exception when calling non-existing static method', function () {
    PaymentType::NON_EXISTING_METHOD();
})->throws(Exception::class);
