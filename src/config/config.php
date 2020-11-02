<?php

return [
    /**
     * Push tracking cookie prefix
     */
    'cookie_prefix' => 'http2_pushed_',

    /**
     * How many days should the push cookie expire in
     */
    'cookie_expires_in' => 30,

    /**
     * Override cookie expire for types
     */
    'cookie_expire_types' => [
        // 'font' => 90,
        // 'script' => 27,
        // 'style' => 24,
    ],

    /**
     * Resources to push on every request
     */
    'always' => [
        // 'app.js', # A simple string
        // [
        //     'src' => 'app.css', # Or an array, containing the src & expires time
        //     'expires' => '90',
        // ],
    ],

    /**
     * Whether to throw an exception if unable to infer the type of resource
     */
    'exception_on_failure' => true,

];
