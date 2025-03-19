<?php
require 'config.php';
require '.env';
require __DIR__ . '/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;

// Ensure correct Cloudinary configuration
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dzn369qpk',
        'api_key'    => '274266766631951',
        'api_secret' => 'ThwRkNdXKQ2LKnQAAukKgmo510g',
    ],
    'url' => [
        'secure' => true
    ]
]);
