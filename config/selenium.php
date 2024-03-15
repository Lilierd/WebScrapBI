<?php

return [
    'server_url'            => env('SELENIUM_SERVER_URL', "http://selenium:4444/"),
    'driver_capabilities'   => \Facebook\WebDriver\Remote\DesiredCapabilities::chrome(),
];
