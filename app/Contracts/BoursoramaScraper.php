<?php

namespace App\Contracts;

use App\Models\MarketShare;
use App\Models\SnapshotIndex;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Util\HtmlElement;

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
        $this->driver->navigate()->to("https://boursorama.com");
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

        // $cookie = $this->driver->manage()->getCookieNamed("BRS_PROFIL");

        // Storage::write("/cookie.txt", $cookie->getValue());

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
        return $this->extractMarketShareDataFromModel(MarketShare::select('url')->findOrFail($marketShareId)->url);
    }

    public function extractMarketShareDataFromModel(MarketShare $marketShare): array|null
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
    //public function extractMarketShareFileFromPage(?MarketShare $marketShare, ?SnapshotIndex $snapshotIndex, string $URL = "https://www.boursorama.com/espace-membres/telecharger-cours/international") : array|null
    public function extractMarketShareFileFromPage(MarketShare $marketShare, string $URL = "https://www.boursorama.com/espace-membres/telecharger-cours/international", bool $force = false): array|null
    {
        // * Gestion de l'iframe de merde qui bloque le clic de la souris vers le bouton
        try {
            if ($this->driver->getCurrentURL() !== $URL) {
                $this->driver->navigate()->to($URL);
            }
            $script = <<<SCRIPT
return document.getElementsByClassName('wall-banner').forEach(e => e.remove());
SCRIPT;
            // dump("Executing antipopup script", $this->driver->executeScript($script));
            $codeTextAreaSelector           = WebDriverBy::id("quote_search_customIndexesList");
            $particulieresValuesSelector    = WebDriverBy::className("c-input-radio-label");
            $fileformatSelector             = WebDriverBy::cssSelector("div[aria-labelledby]");
            $excelSelector                  = WebDriverBy::cssSelector("div[data-value='WALDATA']");
            $submitButtonSelector           = WebDriverBy::cssSelector("input[value='Télécharger']");
            $calendarButtonSelector         = WebDriverBy::cssSelector("button[data-brs-datepicker-opener]");
            $todaySelector                  = WebDriverBy::cssSelector("td.active.day");

            $this->driver->wait(10, 25)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($particulieresValuesSelector));

            $this->driver->findElements($particulieresValuesSelector)[1]
                ->click();
            // sleep(2);

            $this->driver->wait(10, 25)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($codeTextAreaSelector));

            // $this->driver->findElement($codeTextAreaSelector)->clear();
            $this->driver->action()
                ->sendKeys($this->driver->findElement($codeTextAreaSelector), $marketShare->code) //->sendKeys($this->driver->findElement($codeTextAreaSelector), substr($marketShare->isin, 0, 12))
                ->perform();

            //* File format selector
            $this->driver->findElements($fileformatSelector)[4]
                ->click();
            $this->driver->wait(10, 25)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($excelSelector));
            $this->driver->findElement($excelSelector)
                ->click();

            //* Choix de la date
            $this->driver->findElement($calendarButtonSelector)
                ->click();

            $this->driver->wait(10, 25)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($todaySelector));
            $todayDate = $this->driver->findElement($todaySelector)->getAttribute("data-date");

            // dump($todayDate);
            $yesterdayDate = intval(intval($todayDate ?? 0) - 86400000);

            $yesterdaySelector = WebDriverBy::cssSelector("td[data-date='{$yesterdayDate}']");
            $this->driver->findElement($yesterdaySelector)
                ->click();

            // * Clic sur "Télécharger"
            $this->driver->findElement($submitButtonSelector)
                ->click();
        } catch (ElementClickInterceptedException $exceptionPopupDeMerde) {
            if (!$force) {
                // * Si une popup de merde bloque le clic, on éxecute un script qui doit NORMALEMENT la supprimer (28/03/2024) et on relance l'action de clic
                // dump("Executing antipopup script", $this->driver->executeScript($script));

                dump("Retrying one last time");
                return $this->extractMarketShareFileFromPage(marketShare: $marketShare, URL: $URL, force: true);
            }
        } catch (Exception $e) { // * Si y'a un autre problème on throw
            throw $e;
        } finally {

            $absoluteFilePath = $this->seleniumGridDownloadFiles($marketShare);
            dump($absoluteFilePath);
            $fileContent = File::get($absoluteFilePath);

            // dump($fileContent);

            $rawFileArray = preg_split('/(;|\r\n)/', $fileContent);
            $headers = [
                'code',
                'update_date',
                'data_1',
                'data_2',
                'data_3',
                'data_4',
                'volume',
                'currency'
            ];
            $fileArray = array_chunk(
                array: [
                    ...$headers,
                    ...$rawFileArray
                ],
                length: count($headers),
            );
            dump($fileArray);
            return $fileArray;
        }
    }

    public function extractForumMessagesUrlFromPage(MarketShare $marketShare)
    {
        try {
            if ($this->driver->getCurrentURL() !== $marketShare->url)
                $this->driver->navigate()->to($marketShare->url);

            //? MarketShare page
            $allMessagesSelector    = WebDriverBy::className("c-message");
            $messageLinkSelector   = WebDriverBy::cssSelector("a.c-link.c-link--regular.c-link--neutral.c-link--bold.c-link--no-underline");

            $this->driver->wait(10, 25)
                ->until(WebDriverExpectedCondition::presenceOfElementLocated($allMessagesSelector));
            // $allMessages = $this->driver->findElement($allMessagesSelector)->getDomProperty("innerText");

            $urlArray = [];
            foreach ($this->driver->findElements($allMessagesSelector) as $message) {
                //TODO : récup l'URL de chaque message puis refaire une boucle pour naviguer sur chaque msg

                $urlArray[] = $message->findElement($messageLinkSelector)->getDomProperty("href");
            }

            dump($urlArray);

            return $urlArray;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function extractForumMessagesFromPage(MarketShare $marketShare)
    {
        $urlArray = $this->extractForumMessagesUrlFromPage($marketShare);

        try {
            //? Message page
            $messageTitleSelector = WebDriverBy::cssSelector("h1.c-title");
            $messageContentSelector = WebDriverBy::cssSelector("p.c-message__text.c-message__text--shifted");
            $messageAuthorSelector  = WebDriverBy::cssSelector("button[data-popover-target-class='c-profile-light']");
            $messageDateSelector    = WebDriverBy::cssSelector("span.c-source__time");

            $messagesArray = [];
            foreach ($urlArray as $url) {
                $this->driver->navigate()->to($url);

                $this->driver->wait(10, 25)
                    ->until(WebDriverExpectedCondition::presenceOfElementLocated($messageAuthorSelector));

                //$this->driver->executeScript("arguments[0].scrollIntoView();", );

                $messagesArray[] = [
                    'title'     => strip_tags($this->driver->findElement($messageTitleSelector)->getDomProperty('innerText')),
                    'content'   => strip_tags($this->driver->findElement($messageContentSelector)->getDomProperty('innerText')),
                    'author'    => strip_tags($this->driver->findElement($messageAuthorSelector)->getDomProperty('innerText')), //! Marche po, jsp pk
                    'date'      => strip_tags($this->driver->findElement($messageDateSelector)->getDomProperty('innerText'))
                ];
            }

            return $messagesArray;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
