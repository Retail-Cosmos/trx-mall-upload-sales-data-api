<?php

return [
    /**
     * Configuration for the TRX Mall Upload Sales Data API.
     *
     * This configuration specifies the base URI, grant type, username, and password
     * for accessing the TRX Mall API.
     *
     * @var array
     */
    'api' => [
        'base_uri' => env('TRX_MALL_API_BASE_URI'),
        'grant_type' => env('TRX_MALL_API_GRANT_TYPE'),
        'username' => env('TRX_MALL_API_USERNAME'),
        'password' => env('TRX_MALL_API_PASSWORD'),
    ],

    /**
     * Configuration for the trx-mall-upload-sales-data-api.
     *
     * This file contains the configuration options for the trx-mall-upload-sales-data-api.
     * The 'log' option specifies the channel to be used for logging, with a default value of 'stack'.
     */
    'log' => [
        'channel' => env('TRX_MALL_LOG_CHANNEL', 'stack'),
    ],

    /**
     * recipient for the notification
     * mail.name and mail.email are used for the recipient of the email notification
     */
    'notifications' => [
        'mail' => [
            'name' => env('TRX_MALL_API_MAIL_NAME'),
            'email' => env('TRX_MALL_API_MAIL_EMAIL'),
        ],
    ],
];
