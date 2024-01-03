<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Enums;

enum PaymentType: string
{
    case CASH = 'cash';
    case TNG = 'tng';
    case VISA = 'visa';
    case MASTERCARD = 'mastercard';
    case AMEX = 'amex';
    case VOUCHER = 'voucher';
    case OTHERS = 'othersamount';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @throws \Exception
     */
    public static function __callStatic($name, $args): string
    {
        $cases = self::cases();

        foreach ($cases as $case) {
            if ($case->name === $name) {
                return $case->value;
            }
        }

        throw new \Exception("No static method or enum constant '$name' in class ".self::class);
    }
}
