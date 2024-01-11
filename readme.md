# The TRX Mall Upload Sales Data API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/retail-cosmos/trx-mall-upload-sales-data-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/retail-cosmos/trx-mall-upload-sales-data-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/retail-cosmos/trx-mall-upload-sales-data-api.svg?style=flat-square)](https://packagist.org/packages/retail-cosmos/trx-mall-upload-sales-data-api)

The TRX Mall Upload Sales Data API is a Laravel package to upload sales data to TRX Mall. it simplifies the process of uploading sales data to Tangent API.

## Installation

You can install the package via composer:

```bash
composer require retail-cosmos/trx-mall-upload-sales-data-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-config"
```

Please add the following to your `.env` file

```dotenv
# mandatory for the API
TRX_MALL_API_BASE_URI=
TRX_MALL_API_GRANT_TYPE=
TRX_MALL_API_USERNAME=
TRX_MALL_API_PASSWORD=

# optional default is stack
TRX_MALL_LOG_CHANNEL=stack

# name is optional default is Sir/Madam if not provided
# email is mandatory if you want to send the notification email
# we do not set the email we skip sending the notification email
TRX_MALL_NOTIFICATION_MAIL_NAME=
TRX_MALL_NOTIFICATION_MAIL_EMAIL=

# mandatory In Y-m-d format (2024-01-01) 
# It is used to get Batch ID for the sales data
TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD=

```

## Usage

Please follow these steps for sending the sales data to API.

1. Add a [scheduler](https://laravel.com/docs/10.x/scheduling) in your Laravel project to call the command `tangent:send-sales` daily at midnight. It sends the sales for the previous day for each store as returned from the application.

```php
$schedule->command('tangent:send-sales')->daily();
```

> [!NOTE]
> this command will send the sales for the previous day for each store as returned from the application.
> If you wish to send a specific sales, you may pass the following options to the command:
>    - `--date` - Date in the Y-m-d (2024-01-01) format to send sales for a specific date.
>    - `--store_identifier` - To send a sales for a specific store only. check the `app/Services/TrxMallUploadSalesDataService.php` file for the store identifier.

Example:

```bash
php artisan tangent:send-sales --date=2024-01-01 --store_identifier=store1
```
If you want to schedule it for a specific store

```php
$schedule->command('tangent:send-sales --store_identifier=store1')->daily();
```

2. Publish the service by running the following command:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-service"
```

3. Update the `app/Services/TrxMallUploadSalesDataService.php` file to return the stores and sales data:
    
    1. Store data should be an array of stores with the following keys:
        - `store_identifier`: Unique identifier for the store. It will be used to retrieve the sales for the store.
        - `machine_id`: Machine ID.
        - `gst_registered`: Boolean value to indicate if the store is GST registered or not.
    2. Sales data should be an array of sales with the following keys:
        - `happened_at`: Date and time of the sale in the format `Y-m-d H:i:s`.
        - `net_amount`: Net amount.
        - `gst`: GST amount.
        - `discount`: Discount amount.
        - `payments`: Array of payments with the following keys with the amount of the payment after discount and before GST:
            - `cash`, `tng`, `visa`, `mastercard`, `amex`, `voucher`, `othersamount` or you can use `PaymentType` enum provided by the package.
                ```php
                    use RetailCrm\TrxMallUploadSalesDataApi\Enums\PaymentType;
        
                    PaymentType::CASH(); // cash
                    PaymentType::TNG(); // tng
                    PaymentType::VISA(); // visa
                    PaymentType::MASTERCARD(); // mastercard
                    PaymentType::AMEX(); // amex
                    PaymentType::VOUCHER(); // voucher
                    PaymentType::OTHERS(); // othersamount
                ```

4. If you want to customize the notification email, you can publish the notification view by running the following command:

```bash
php artisan vendor:publish --tag="trx-mall-upload-sales-data-api-view"
```

> [!NOTE]
> We will calculate Batch ID for the sales data based on the `TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD` in the `.env` file. 
> We will find the difference between the `TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD` and the date which we are sending the sales data for and add 1 to it. 
> For example, if the `TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD` is `2024-01-01` and we are sending the sales data for `2024-01-02` then the batch ID will be `2`.

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
