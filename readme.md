# The TRX Mall Upload Sales Data Api

[![Latest Version on Packagist](https://img.shields.io/packagist/v/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)

The TRX Mall Upload Sales Data Api is a Laravel package to upload sales data to TRX Mall. it simplifies the process of uploading sales data to TRX Mall.

## Installation

You can install the package via composer:

```bash
composer require retail-cosmos/trx-mall-upload-sales-data-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-config"
```

please add the following to your `.env` file

```dotenv
TRX_MALL_API_BASE_URI=
TRX_MALL_API_GRANT_TYPE=
TRX_MALL_API_USERNAME=
TRX_MALL_API_PASSWORD=

# optional default is stack
TRX_MALL_LOG_CHANNEL=

```


## Usage

Please follow these steps for the sending the sales data to API.

1. Add a [scheduler](https://laravel.com/docs/10.x/scheduling) in your Laravel project to call the command `tangent:send-sales` daily at midnight. It send the sales for the previous day for each store as returned from the application.

```php
$schedule->command('tangent:send-sales')->daily();
```

> [!TIP]
> If you wish to send a specific sales, you may pass the following options to the command:
>    - `date` - Date in the YYYY-MM-DD format to send a sales for a specific date.
>    - `store_identifier` - To send a sales for a specific store only.

2. publish the service by running the following command

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-service"
```

3. update the `app/Services/TrxMallUploadSalesDataService.php` file to return the store data and sales data.

    1. add getStores method to return the store data
    
    ```php
    public function getStores(string $storeIdentifier = null): array
    {
        return [
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
    }
    ```
    2. add getSales method to return the sales data
    
    ```php
    use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;
    public function getSales(string $storeIdentifier = null, string $date = null): Collection
    {
        return collect([
            [
                'happened_at' => '2023-01-01 00:00:00',
                'net_amount' => 191.54,
                'gst' => 1.55,
                'discount' => 0,
                'payments' => [
                    'cash' => 18.97,
                    'tng' => 0,
                    'visa' => 176.78,
                    'mastercard' => 0,
                    'amex' => 0,
                    'voucher' => 0,
                    'othersamount' => 0,
                ],
            ], [
                'happened_at' => '2023-01-01 00:00:00',
                'net_amount' => 391.54,
                'gst' => 12.65,
                'discount' => 10,
                'payments' => [
                    // you can use the PaymentType enum to get the payment type
                    PaymentType::CASH() => 18.97,
                    PaymentType::TNG() => 0,
                    PaymentType::VISA() => 176.78,
                    PaymentType::MASTERCARD() => 0,
                    PaymentType::AMEX() => 47.80,
                    PaymentType::VOUCHER() => 0,
                    PaymentType::OTHERS() => 0,
                ],
            ],
        ]);
    }
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
