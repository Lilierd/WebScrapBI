<?php

namespace App\Contracts;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\HttpCommandExecutor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCommand;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * Un scraper basé sur Selenium
 */
abstract class AbstractScraper
{
    /**
     *
     */
    protected RemoteWebDriver $driver;

    /**
     *
     */
    public function __construct()
    {
        // dump("Creating an AbstractScrapper");
        $seleniumServerUrl = config('selenium.server_url');
        $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['--start-maximized']);
        // $chromeOptions->addArguments(['--start-fullscreen']);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->driver = RemoteWebDriver::create(
            selenium_server_url: $seleniumServerUrl,
            desired_capabilities: $desiredCapabilities,
            connection_timeout_in_ms: 5000,
            request_timeout_in_ms: 5000,
        );
    }

    /**
     * Called upon object is freed from RAM
     */
    public function __destruct()
    {
        // dump("Calling destructor for AbstractScraper", $this);
        $this->driver->quit();
    }
}
