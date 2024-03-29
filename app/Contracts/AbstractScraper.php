<?php

namespace App\Contracts;

use App\Models\MarketShare;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\HttpCommandExecutor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCommand;
use Illuminate\Console\OutputStyle;
use Illuminate\Http\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

/**
 * Un scraper basé sur Selenium
 */
abstract class AbstractScraper
{
    // public RemoteWebDriver $driver;
    static string $POST_GET_FILES = '/session/:sessionId/se/files';

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
            request_timeout_in_ms: 360000000
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
        $this->driver->quit();
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
            // "--disable-extensions",
            "--enable-managed-downloads",
            // "--user-data-dir=/home/seluser/selenium",
            // "--proxy-server='direct://'",
            // "--proxy-bypass-list=*",
            // "--disable-dev-shm-usage",
            // "--ignore-certificate-errors",
            "--user-agent= Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36"
        ]);

        if ($HEADLESS) {
            $chromeOptions->addArguments([
                "--headless",
                "--disable-gpu",
                "--no-sandbox",
            ]);
        } else {
            $chromeOptions->addArguments([
                "--start-maximized",
                // "--window-size=1920,1080"
            ]);
        }

        $desiredCapabilities->setCapability('se:downloadsEnabled', true);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        return $desiredCapabilities;
    }

    private static function getSeleniumHubUrl(): string
    {
        return config('selenium.server_url', "http://selenium:4444");
    }

    protected function seleniumGridDownloadFiles(MarketShare $marketShare): string|null
    {
        //Get files names from selenium grid
        dump("seleniumGridDownloadFiles waiting");
        sleep(5);
        $files = $this->driver->executeCustomCommand('/session/:sessionId/se/files');

        // dump($files);
        // For multiple files if needed
        // * Saved file name in Laravel
        $fileName = "$marketShare->code" . DIRECTORY_SEPARATOR . Carbon::parse(now())->format("Y-m-d_H-i");
        $zipFileName = "{$fileName}.zip";
        $txtFileName = "{$fileName}.txt";
        foreach ($files['names'] as $file) {
            // Set file to download
            $file_to_download = [
                'name' => $file,
            ];

            // Get file content from selenium grid to local
            $file_content = $this->driver->executeCustomCommand('/session/:sessionId/se/files', 'POST', $file_to_download);
            // dump($file_content);
            // dump($fileObject);
            $file_content_encoded = $file_content['contents'];

            $zipfilePath = Storage::disk('public')->path($zipFileName);
            $txtfilePath = Storage::disk('public')->path($txtFileName);
            // * Check directory
            if (!Storage::disk('public')->exists($marketShare->code)) {
                Storage::disk('public')->makeDirectory($marketShare->code);
            }
            // * Saved temp file
            $file_content_decoded = base64_decode($file_content_encoded);
            dump(Storage::disk('public')->put(path: $zipFileName, contents: $file_content_decoded));
            // * Dezip
            // dump(Storage::disk('public')->get(path: $zipFileName));
            $zip = new ZipArchive;
            $zip->open(public_path('storage' . DIRECTORY_SEPARATOR . $zipFileName));
            $textFile = $zip->getFromIndex(0);
            // $zip->extractTo(Storage::disk('public')->path($marketShare->code));
            $zip->close();
            Storage::disk('public')->put(path: $txtFileName, contents: $textFile);
            Storage::disk('public')->delete($zipFileName);
        }
        return Storage::disk('public')->path($txtFileName);
    }
}
