<?php

namespace App\Contracts;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * Un scraper basé sur Selenium
 */
interface AbstractScraper {

    // public function __construct(string $seleniumServerUrl = "http://selenium:4444")
    // {
    //     $seleniumServerUrl = $seleniumServerUrl ?? config('selenium.server_url');

    //     $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
    //     $chromeOptions = new ChromeOptions();
    //     $chromeOptions->addArguments(['--start-maximized']);
    //     // $chromeOptions->addArguments(['--start-fullscreen']);
    //     $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

    //     parent::create(
    //         selenium_server_url: $seleniumServerUrl,
    //         desired_capabilities: $desiredCapabilities,
    //     );
    // }
}
