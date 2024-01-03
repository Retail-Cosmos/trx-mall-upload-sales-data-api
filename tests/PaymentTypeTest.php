<?php

use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

it('returns all enum values', function () {
    $expectedValues = [
        'cash',
        'tng',
        'visa',
        'mastercard',
        'amex',
        'voucher',
        'othersamount',
    ];

    $values = PaymentType::values();

    expect($values)->toEqual($expectedValues);
});

it('returns enum value by calling static method', function () {
    $paymentType = PaymentType::CASH();

    expect($paymentType)->toBe('cash');
});

it('throws exception when calling non-existing static method', function () {
    PaymentType::NON_EXISTING_METHOD();
})->throws(Exception::class, "No static method or enum constant 'NON_EXISTING_METHOD' in class RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType");
