# This is a Laravel package providing the functionality to upload sales data to the TRX mall via APIs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)


## Installation

You can install the package via composer:

```bash
composer require retail-cosmos/trx-mall-upload-sales-data-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-config"
```

This is the contents of the published config file:

```php
return [
];
```


## Usage

### example data

```php
$data = collect([
    [
        'made_at' => '2023-01-01 00:00:00',
        'gto' => 191.54,
        'gst' => 1.55,
        'discount' => 0,
        'service_charge' => 5.00,
        'no_of_persons' => 0,
        PaymentType::CASH() => 8.97,
        PaymentType::TNG() => 0,
        PaymentType::VISA() => 76.78,
        PaymentType::MASTERCARD() => 0,
        PaymentType::AMEX() => 47.80,
        PaymentType::VOUCHER() => 0,
        PaymentType::OTHERS() => 57.99,
    ], [
        'made_at' => '2023-01-01 00:00:00',
        'gto' => 391.54,
        'gst' => 12.65,
        'discount' => 10,
        'service_charge' => 0.00,
        'no_of_persons' => 0,
        PaymentType::CASH() => 18.97,
        PaymentType::TNG() => 0,
        PaymentType::VISA() => 176.78,
        PaymentType::MASTERCARD() => 0,
        PaymentType::AMEX() => 47.80,
        PaymentType::VOUCHER() => 0,
        PaymentType::OTHERS() => 0,
    ],
]);

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Punyapal Shah](https://github.com/MrPunyapal])
- [Gaurav Makhecha](https://github.com/gauravmak)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
