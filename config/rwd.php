<?php

return [
    'driver_url'            => env('SELENIUM_URL', "http://selenium:4444/wd/hub"),
    'driver_capabilities'   => \Facebook\WebDriver\Remote\DesiredCapabilities::chrome(),
];
