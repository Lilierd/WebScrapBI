<?php

namespace App\Contracts;

use App\Models\MarketShare;
use App\Models\SnapshotIndex;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Facades\Storage;

/**
 * Une concrétisation de l'Abstraction qu'est un robot navigant sur un navigateur chrome, en l'occurence sur le site de boursorama.
 */
class BoursoramaScraper extends AbstractScraper
{
    /**
     * Constructor
     * @param ?string $seleniumServerUrl - Commonly https://selenium:4444 inside same docker host
     */
    // public function __construct(?string $seleniumServerUrl = null)
    // {
    //     $seleniumServerUrl = $seleniumServerUrl ?? config('selenium.server_url');

    //     $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
    //     $chromeOptions = new ChromeOptions();
    //     $chromeOptions->addArguments(['--start-maximized']);
    //     // $chromeOptions->addArguments(['--start-fullscreen']);
    //     $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

    //     // $this = RemoteWebDriver::create(
    //     //     selenium_server_url: $seleniumServerUrl,
    //     //     desired_capabilities: $desiredCapabilities,
    //     // );
    // }



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
        $this->driver->get($boursoramaBaseUrl);
        $this->driver->wait(10, 25)
            ->until(WebDriverExpectedCondition::presenceOfElementLocated($popupDeMerdeSelector));
        $this->driver->findElement($popupDeMerdeSelector);

        // * Si on passe aux lignes suivantes, c'est que la "pop up de merde" est bien présente, sinon on ira dans le bloc catch ;)
        $this->driver->findElement($popupDeMerdeSelector)
            ->findElement($inputClosePopupDeMerdeSelector)
            ->click();

        // $this->snapshot();

        $this->driver->wait(10, 25)->until(WebDriverExpectedCondition::presenceOfElementLocated($loginFormButtonSelector));
        $this->driver->findElement($loginFormButtonSelector)
        ->click();


        $this->driver->wait(10, 25)
            ->until(WebDriverExpectedCondition::presenceOfElementLocated(
                $inputUsernameSelector
            ));
        $this->driver->action()
            ->sendKeys($this->driver->findElement($inputUsernameSelector), $username)
            ->sendKeys($this->driver->findElement($inputPasswordSelector), $password)
            ->perform();
        $this->driver->findElement($inputSubmitSelector)->click();

        //! Ne pas changer l'URL parce que là on attend la redirection en fait hein, c'est chiant sinon
        $this->driver->wait(10, 25)->until(WebDriverExpectedCondition::presenceOfElementLocated($dataUsernameSelector));

        $dataUsernameElement = $this->driver->findElement($dataUsernameSelector);
        $dataUsernameString = trim($dataUsernameElement->getDomProperty("innerText"));

        $cookie = $this->driver->manage()->getCookieNamed("BRS_PROFIL");

        Storage::write("/cookie.txt", $cookie->getValue());

        return $dataUsernameString;
    }

    /**
     * Extrait les données d'une action précise.
     *
     */
    public function extractMarketShareDataFromUrl(string $url): array|null
    {
        $marketShareData = null;
        try {
            $this->driver->navigate()->to($url);

            $selectorName = WebDriverBy::cssSelector('a.c-faceplate__company-link');
            $selectorIsin = WebDriverBy::cssSelector('h2.c-faceplate__isin');
            $selectorLastValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $selectorHighValue = WebDriverBy::cssSelector('span[data-ist-high]');
            $selectorLowValue = WebDriverBy::cssSelector('span[data-ist-low]');
            $selectorOpenValue = WebDriverBy::cssSelector('span[data-ist-open]');
            $selectorCloseValue = WebDriverBy::cssSelector('span[data-ist-previousclose]');
            $selectorVolume = WebDriverBy::cssSelector('span[data-ist-totalvolume]');

            $this->driver->wait(10, 25)
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

            $this->driver->wait(10, 25)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorVolume));

            $marketShareData = [ // Nomenclature relative aux tables Models de Eloquent (snake_case)
                'name'              => $dataName,
                'isin'              => $dataIsin,
                'last_value'        => floatval($dataLastValue),
                'high_value'        => floatval($dataHighValue),
                'low_value'         => floatval($dataLowValue),
                'open_value'        => floatval($dataOpenValue),
                'close_value'       => floatval($dataCloseValue),
                'volume'            => intval($dataVolume),
                'url'               => $url,
            ];
        } catch (Exception $exception) {
            throw $exception;
        }
        return $marketShareData;
    }

    /**
     * Extrait les données du cours d'une action précise.
     */
    public function extractMarketShareData(int $marketShareId): array|null
    {
        return $this->extractMarketShareDataFromUrl(MarketShare::select('url')->findOrFail($marketShareId)->url);
    }

    public function extractMarketShareDataFromModel(MarketShare $marketShare) : array|null
    {
        return $this->extractMarketShareDataFromUrl($marketShare->url);
    }

    /**
     * Retourne les pages de navigation disponibles
     */
    public function extractNavigationPages(string $URL = "https://www.boursorama.com/bourse/actions/cotations/"): array|null
    {
        // * Initialization
        $navigationURLS = [];
        $selectorPagination = WebDriverBy::cssSelector('div[role=navigation]');
        $selectorPages = WebDriverBy::cssSelector('a');

        // *
        // if ($this->driver->getCurrentURL() !== $URL) {
            $this->driver->navigate()->to($URL);
        // }

        $this->driver->wait(10, 25)
            ->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorPagination));
        $navigation = $this->driver->findElement($selectorPagination);

        $pages = $navigation->findElements($selectorPages);

        $navigationURLS = array_map(function (RemoteWebElement $element) {
            return $element->getDomProperty("href");
        }, $pages);

        return $navigationURLS;
    }

    /**
     * Retourne un tableau d'URLs d'actions depuis les pages de navigation de Boursorama
     */
    public function extractMarketSharesUrlsFromPage(string $URL = "https://www.boursorama.com/bourse/actions/cotations/"): array|null
    {
        // if ($this->driver->getCurrentURL() !== $URL) {
            $this->driver->navigate()->to($URL);
        // }
        // * Initialization
        $data = [];
        $selectorTable = WebDriverBy::cssSelector("table.c-table.c-table--generic.c-table--generic.c-shadow-overflow__table-fixed-column.c-table-top-flop");
        $selectorLinks = WebDriverBy::cssSelector("tr td a");

        // *
        $this->driver->wait(10, 25)
            ->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorTable));
        $table = $this->driver->findElement($selectorTable);
        $pages = $table->findElements($selectorLinks);

        $data = array_map(function (RemoteWebElement $element) {
            return $element->getDomProperty("href");;
        }, $pages);
        return $data;
    }

    //TODO: extraire le fichier et le sauvegarder en base en lien avec un snapshot Index
    // ? Pourquoi pas le sauvegarder avec comme date de départ = 1 Janvier 1970 et date de fin = SnapshotIndex snapshot_time (comme ça on est plus fiable)
    public function extractMarketShareFileFromPage(?MarketShare $marketShare, ?SnapshotIndex $snapshotIndex, string $URL = "https://www.boursorama.com/espace-membres/telecharger-cours/international") : array|null
    {
        // if($this->driver->getCurrentURL() !== $URL)
        // {
            $this->driver->navigate()->to($URL);
        // }

        // $this->onMarketShareFileSearchFor($marketShare);

        $codeTextAreaSelector           = WebDriverBy::id("quote_search_customIndexesList");
        $particulieresValuesSelector    = WebDriverBy::className("c-input-radio-label");
        $submitButtonSelector           = WebDriverBy::cssSelector("input[value='Télécharger']");

        $this->driver->wait(10, 25)
            ->until(WebDriverExpectedCondition::presenceOfElementLocated($codeTextAreaSelector));

        $this->driver->action()
            ->sendKeys($this->driver->findElement($codeTextAreaSelector), substr($marketShare->isin, 0, 12))
            ->perform();

        // $this->driver->

        return null;
    }


}
