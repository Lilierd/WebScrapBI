<?php

namespace App\Contracts;

use App\Models\MarketShare;
use App\Models\SnapshotIndex;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;



/**
 * Une concrétisation de l'Abstraction qu'est un robot navigant sur un navigateur chrome, en l'occurence sur le site de boursorama.
 */
class BoursoramaScraper implements AbstractScraper
{
    public $driver = null;

    public function __construct(?string $seleniumServerUrl = null)
    {
        $seleniumServerUrl = $seleniumServerUrl ?? config('selenium.server_url');

        $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['--start-maximized']);
        // $chromeOptions->addArguments(['--start-fullscreen']);
        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->driver = RemoteWebDriver::create(
            selenium_server_url: $seleniumServerUrl,
            desired_capabilities: $desiredCapabilities,
        );
    }

    /**
     * Constructeur
     */   /**
     * Authentifie le driver sur le site de https://boursorama.com/
     */
    public function login()
    {
        // * Initialisation des variables
        //? Compte utilisateur
        $username                           = config('boursorama.username');
        //? MDP Utilisateur
        $password                           = config('boursorama.password');
        //? URL de navigation pour la connexion
        $boursoramaBaseUrl                  = config('boursorama.base_url');
        //? Bouton "Espace Membre"
        $loginFormButtonSelector            = WebDriverBy::id("login-member");
        //? Formulaire de connexion
        $loginFormSelector                  = WebDriverBy::cssSelector("form[name=login_member]");
        //? Champs "username" (Formulaire de connexion)
        $inputUsernameSelector              = WebDriverBy::id("login_member_login");
        //? Champs "password" (Formulaire de connexion)
        $inputPasswordSelector              = WebDriverBy::id("login_member_password");
        //? Bouton "Connexion" (Formulaire de connexion)
        $inputSubmitSelector                = WebDriverBy::id("login_member_connect");
        //? Bouton "Continuer sans accepter" (Pop-up de merde)
        $inputClosePopupDeMerdeSelector     = WebDriverBy::cssSelector('span.didomi-continue-without-agreeing');
        //? Pop-up de merde
        $popupDeMerdeSelector               = WebDriverBy::cssSelector('div.didomi-popup-view');
        //? Badge comportant le nom d'utilisateur
        $dataUsernameSelector               = WebDriverBy::cssSelector('span.c-navigation__header-logged-member');

        // * Debut du parcours vers la page de connexion
        try {
            $this->driver->get($boursoramaBaseUrl);

            try {
                $this->driver->wait(5, 1)
                    ->until(WebDriverExpectedCondition::presenceOfElementLocated($popupDeMerdeSelector));
                $this->driver->findElement($popupDeMerdeSelector);

                // * Si on passe aux lignes suivantes, c'est que la "pop up de merde" est bien présente, sinon on ira dans le bloc catch ;)
                $this->driver->findElement($popupDeMerdeSelector)
                    ->findElement($inputClosePopupDeMerdeSelector)
                    ->click();
            } catch (Exception $e) {
                throw $e;
                // $this->driver->error('While logging in, tried to intercept cookies relative popup but was unfortunately not here.'); //! Ca va être dur le TOEIC
            } finally {
                //TODO: Virer si pas de traitement supplémentaire
                // $bar->advance();
            }

            $this->driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($loginFormButtonSelector));
            $this->driver->findElement($loginFormButtonSelector)
                ->click();

            try {
                $this->driver->wait(5, 1)
                    ->until(WebDriverExpectedCondition::presenceOfElementLocated(
                        $inputUsernameSelector
                    ));
            } catch (Exception $e) {
                throw $e;

                // $this->driver->error("Form didn't showed up. Aizekyel burnt to death.");
            } finally {
                // $bar->advance();
            }

            $this->driver->action()
                ->sendKeys($this->driver->findElement($inputUsernameSelector), $username)
                ->sendKeys($this->driver->findElement($inputPasswordSelector), $password)
                ->perform();
            $this->driver->findElement($inputSubmitSelector)->click();
        } catch (Exception $e) {
            // $this->driver->output->error("Sa mere bloqué");
            throw $e;
        } finally {
            //! Ne pas changer l'URL parce que là on attend la redirection en fait hein, c'est chiant sinon
            $this->driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($dataUsernameSelector));

            $dataUsernameElement = $this->driver->findElement($dataUsernameSelector);
            $dataUsernameString = trim($dataUsernameElement->getDomProperty("innerText"));

            $this->driver->quit();
        }
    }

    public function extractMarketShareSnapshotDataFromUrl(string $url)
    {
        try {
            $this->driver->get($url);
            // sleep(1);

            $selectorName = WebDriverBy::cssSelector('a.c-faceplate__company-link');
            $selectorIsin = WebDriverBy::cssSelector('h2.c-faceplate__isin');
            $selectorLastValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $selectorHighValue = WebDriverBy::cssSelector('span[data-ist-high]');
            $selectorLowValue = WebDriverBy::cssSelector('span[data-ist-low]');
            $selectorOpenValue = WebDriverBy::cssSelector('span[data-ist-open]');
            $selectorCloseValue = WebDriverBy::cssSelector('span[data-ist-previousclose]');
            $selectorVolume = WebDriverBy::cssSelector('span[data-ist-totalvolume]');

            $this->driver->wait(5)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorVolume));
            $dataName = $this->driver->findElement($selectorName)->getDomProperty("innerText");
            $dataIsin = $this->driver->findElement($selectorIsin)->getDomProperty("innerText");
            $dataLastValue = $this->driver->findElement($selectorLastValue)->getDomProperty("innerText");
            $dataHighValue = $this->driver->findElement($selectorHighValue)->getDomProperty("innerText");
            $dataLowValue = $this->driver->findElement($selectorLowValue)->getDomProperty("innerText");
            $dataOpenValue = $this->driver->findElement($selectorOpenValue)->getDomProperty("innerText");
            $dataCloseValue = $this->driver->findElement($selectorCloseValue)->getDomProperty("innerText");
            $dataVolume = $this->driver->findElement($selectorVolume)->getDomProperty("innerText");
            // $dataTime = null; //TODO: Mettre dans date de maintenace

            $this->driver->wait(5, 1)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorVolume));

            $marketShareData = [ // Nomenclature relative aux tables Models de Eloquent (snake_case)
                'name' => $dataName,
                'isin' => $dataIsin,
                'last_value' => floatval($dataLastValue),
                'high_value' => floatval($dataHighValue),
                'low_value' => floatval($dataLowValue),
                'open_value' => floatval($dataOpenValue),
                'close_value' => floatval($dataCloseValue),
                'volume' => intval($dataVolume),
            ];

            return $marketShareData;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
