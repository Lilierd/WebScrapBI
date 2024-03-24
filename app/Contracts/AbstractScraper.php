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
        // $chromeOptions->addArguments(['--start-maximized']);
        // $chromeOptions->addArguments(['--headless']);

        // $chromeOptions->addArguments(['--start-fullscreen']);

        $chromeOptions->addArguments(["--window-size=1920,1080"]);
        $chromeOptions->addArguments(["--disable-extensions"]);
        $chromeOptions->addArguments(["--proxy-server='direct://'"]);
        $chromeOptions->addArguments(["--proxy-bypass-list=*"]);
        $chromeOptions->addArguments(["--start-maximized"]);
        $chromeOptions->addArguments(['--headless']);
        $chromeOptions->addArguments(['--disable-gpu']);
        $chromeOptions->addArguments(['--disable-dev-shm-usage']);
        $chromeOptions->addArguments(['--no-sandbox']);
        $chromeOptions->addArguments(['--ignore-certificate-errors']);
        $chromeOptions->addArguments(['--user-agent= Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36']);


        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        // $desiredCapabilities->setCapability("pageLoadStrategy", 'none');

        $this->driver = RemoteWebDriver::create(
            selenium_server_url: $seleniumServerUrl,
            desired_capabilities: $desiredCapabilities,
            connection_timeout_in_ms: 10 * 1000,
            request_timeout_in_ms: 10 * 1000,
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

    protected function takeScreenshot()
    {
        $now = now()->format("Y-m-d_H:i:s");
        $name = "{$now}_{$this->driver->getSessionID()}.png";
        $this->driver->takeScreenshot(storage_path($name));
        // dd($this->driver->getPageSource());
    }
}
