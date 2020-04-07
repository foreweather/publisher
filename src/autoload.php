<?php

use Phalcon\Loader;

require_once 'vendor/autoload.php';

$loader = new Loader();
$loader->registerNamespaces(
    [
        'Foreweather' => 'library/Foreweather',
        'Providers'   => 'application/Providers',
    ]
);
$loader->register();
