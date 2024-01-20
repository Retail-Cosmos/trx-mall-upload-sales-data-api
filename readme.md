# The TRX Mall Upload Sales Data API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)

The TRX Mall Upload Sales Data API is a Laravel package to upload sales data to TRX Mall via API.

## Installation

1. Install the package via composer:

```bash
composer require retail-cosmos/trx-mall-upload-sales-data-api
```

2. Publish the config file:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-config"
```

3. Read the config file options and set `.env` variables accordingly.


## Usage

1. You need to add code in your Laravel app to share the sales data with the package. A service class needs to be added for the same. We provide an option to generate the class with a single command:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-service"
```

2. A new class is added at `app/Services/TrxMallUploadSalesDataApiService.php`. It contains a couple of methods to return the stores and sales data as per the requirements. You may make those methods dynamic by changing the code. You may check [this stub](stubs/TrxMallUploadSalesDataApiService.php) file anytime for future reference.

3. Add a [scheduler](https://laravel.com/docs/10.x/scheduling) in your [Laravel](https://laravel.com) project to call the command `trx:send-sales` daily at midnight. It sends the sales for the previous day for each store as returned from the application.

```php
$schedule->command('trx:send-sales')->daily();
```

## Notes

1. The sales command sends the sales for the previous day for each store by default. If you wish to send sales for a specific date/store, you may pass the following options to the command:
    - `--date` - Date in the Y-m-d (2024-12-31) format to send sales for a specific date.
    - `--store_identifier` - To send a sales for a specific store only. check the [stub file](stubs/TrxMallUploadSalesDataApiService.php) for the store identifier.

Example:
```bash
php artisan trx:send-sales --date=2024-11-31 --store_identifier=store1
```

2. If you have set respective .env variables, this package sends notification emails after successful/failed attempt to send sales data to TRX. If you want to customize the notification email, you can publish the view by running the following command:
```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-view"
```

3. The package calculates 'Batch ID' for the sales data based on the `TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD` in the `.env` file. It finds the difference between the date specified for `TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD` and the date for which you are attempting to send the sales data and adds one(1) to it. For example, if the `TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD` is set to `2024-01-10` and you are attempting to send the sales data for `2024-01-20` then the batch ID will be '11'.


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
