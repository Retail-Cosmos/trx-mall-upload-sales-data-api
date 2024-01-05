<?php

return [

    /**
     * This configuration specifies the base URI, grant type, username, and password
     * for accessing the TRX Mall API.
     */
    'api' => [
        'base_uri' => env('TRX_MALL_API_BASE_URI'),
        'grant_type' => env('TRX_MALL_API_GRANT_TYPE'),
        'username' => env('TRX_MALL_API_USERNAME'),
        'password' => env('TRX_MALL_API_PASSWORD'),
    ],

    /**
     * this date will help us to determine batch_id
     * for first date it will be 1 then 2 and so on
     * this date should be in Y-m-d format
     */
    'date_of_first_sales' => env('TRX_MALL_DATE_OF_FIRST_SALES', '2023-01-01'),

    /**
     * The 'log' option specifies the channel to be used for logging,
     * with a default value of 'stack'.
     */
    'log' => [
        'channel' => env('TRX_MALL_LOG_CHANNEL', 'stack'),
    ],
];
