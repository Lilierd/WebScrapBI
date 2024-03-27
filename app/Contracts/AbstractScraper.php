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
    // public RemoteWebDriver $driver;

    /**
     *
     */
    public function __construct(public ?RemoteWebDriver $driver = null)
    {
        dump("Creating a RemoteWebDriver");
        $this->driver = $driver ?? RemoteWebDriver::create(
            selenium_server_url: static::getSeleniumHubUrl(),
            desired_capabilities: static::getCapabilities(),
            connection_timeout_in_ms: 360000000, //30min
            request_timeout_in_ms: 360000000,    //30min
        );
    }


    public static function resumeSession(string $sid)
    {
        return new static(RemoteWebDriver::createBySessionID(
            $sid,
            static::getSeleniumHubUrl(),
            360000000,
            360000000,
            true,
            static::getCapabilities(),
        ));
    }

    /**
     * Called upon object is freed from RAM
     */
    public function __destruct()
    {
        dump("Calling destructor for AbstractScraper");
        // $this->driver->quit();
    }

    public function takeScreenshot()
    {
        $now = now()->format("Y-m-d_H:i:s");
        $name = "{$now}_{$this->driver->getSessionID()}.png";
        $this->driver->takeScreenshot(storage_path($name));
        // dd($this->driver->getPageSource());
    }


    private static function getCapabilities(bool $HEADLESS = false): DesiredCapabilities
    {
        $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
        $chromeOptions = new ChromeOptions();

        $chromeOptions->addArguments([
            "--user-data-dir=/home/seluser/selenium"
        ]);

        $chromeOptions->addArguments([
            "--disable-extensions",
            "--proxy-server='direct://'",
            "--proxy-bypass-list=*",
            "--disable-gpu",
            "--disable-dev-shm-usage",
            "--no-sandbox",
            "--ignore-certificate-errors",
            "--user-agent= Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36"
        ]);

        if ($HEADLESS) {
            $chromeOptions->addArguments(["--headless"]);
        } else {
            $chromeOptions->addArguments(["--start-maximized"]);
        }
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        return $desiredCapabilities;
    }

    private static function getSeleniumHubUrl(): string
    {
        return config('selenium.server_url', "http://selenium:4444");
    }
}
