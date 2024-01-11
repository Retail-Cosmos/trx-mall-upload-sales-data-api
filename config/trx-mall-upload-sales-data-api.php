<?php

return [

    /**
     * Configuration for accessing the TRX Mall API.
     * Specifies the base URI, grant type, username, and password.
     */
    'api' => [
        'base_uri' => env('TRX_MALL_API_BASE_URI'),
        'grant_type' => env('TRX_MALL_API_GRANT_TYPE'),
        'username' => env('TRX_MALL_API_USERNAME'),
        'password' => env('TRX_MALL_API_PASSWORD'),
    ],

    /**
     * Date used to determine the batch_id for sales uploads.
     * The batch_id increments based on the date.
     * for first date it will be 1, for second date it will be 2 and so on.
     * Format: Y-m-d (e.g. 2024-12-31)
     */
    'date_of_first_sales_upload' => env('TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD'),

    /**
     * Logging configuration.
     * Specifies the channel to be used for logging.
     */
    'log' => [
        'channel' => env('TRX_MALL_LOG_CHANNEL', 'stack'),
    ],

    /**
     * ignore if you don't want to receive email notification
     *
     * Notification recipient configuration.
     * Specifies the name and email for email notifications.
     * Default name: 'sir/madam'
     */
    'notifications' => [
        'mail' => [
            'name' => env('TRX_MALL_NOTIFICATION_MAIL_NAME', 'sir/madam'),
            'email' => env('TRX_MALL_NOTIFICATION_MAIL_EMAIL'),
        ],
    ],
];
