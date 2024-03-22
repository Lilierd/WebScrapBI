<?php

namespace App\Console\Commands;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;
use Exception;
use App\Models\MarketShare;

class ScrapeMarketSharesFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-market-shares-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(RemoteWebDriver $driver)
    {
        $marketShares = MarketShare::find(1);   //TODO: remettre ::all(); une fois le foreach mis en place


        try {

            $this->loginDriver($driver);

            $driver->get("https://www.boursorama.com/espace-membres/telecharger-cours/paris");

            $codeTextAreaSelector = WebDriverBy::id("quote_search_customIndexesList");
            $particulieresValuesSelector = WebDriverBy::className("c-input-radio-label");
            $submitButtonSelector = WebDriverBy::cssSelector("input[value='Télécharger']");

            $driver->findElements($particulieresValuesSelector)[1]
                ->click();

            $driver->action()
                ->sendKeys($driver->findElement($codeTextAreaSelector), substr($marketShares->isin, 0, 12))
                ->perform();

            //TODO: Sélectioner le bon type de fichier : "Waldata, Actionbourse"

            //! permet de cliquer pour dl le fichier
            $driver->findElement($submitButtonSelector)
                ->click();

            sleep(2);

            /* foreach ($marketShares as $marketShare) {
                try {

                    $code = $marketShare->isin;

                } catch (Exception $e) {
                    $this->error("Error on {$marketShare->name}");
                    $this->line($e);
                }
            } */

            $code = $marketShares->isin;

            /* $selectorInputCode = WebDriverBy::cssSelector('input#quote_search_customIndexesList');
            $inputCode = $driver->findElement($selectorInputCode)->click();
            $driver->getKeyboard()->sendKeys($code); */
        } catch (Exception $e) {
            throw $e;
        } finally {
            $driver->quit();
        }
    }

    //TODO: On va créer une classe qui regroupe les actions d'un driver pour scraper Boursorama (dans un but de non répétition du code et de limitation des bugs)
    public function loginDriver(RemoteWebDriver $driver)
    {
        $bar = $this->output->createProgressBar(3);
        $bar->start();
        // * Initialisation des variables
        $username                           = config('boursorama.username');
        $boursoramaBaseUrl                  = config('boursorama.base_url');
        $loginFormButtonSelector            = WebDriverBy::id("login-member");
        $password                           = config('boursorama.password');
        $inputUsernameSelector              = WebDriverBy::id("login_member_login");
        $loginFormSelector                  = WebDriverBy::cssSelector("form[name=login_member]");
        $inputSubmitSelector                = WebDriverBy::id("login_member_connect");
        $inputPasswordSelector              = WebDriverBy::id("login_member_password");
        $inputClosePopupDeMerdeSelector     = WebDriverBy::cssSelector('span.didomi-continue-without-agreeing');
        $popupDeMerdeSelector               = WebDriverBy::cssSelector('div.didomi-popup-view');
        $dataUsernameSelector               = WebDriverBy::cssSelector('span.c-navigation__header-logged-member');

        // * Debut du parcours vers la page de connexion
        try {
            $driver->get($boursoramaBaseUrl);

            try {
                $driver->wait(5, 1)
                    ->until(WebDriverExpectedCondition::presenceOfElementLocated($popupDeMerdeSelector));
                $driver->findElement($popupDeMerdeSelector);

                //? Si on passe aux lignes suivantes, c'est que la "pop up de merde" est bien présente, sinon on ira dans le bloc catch ;)
                $driver->findElement($popupDeMerdeSelector)
                    ->findElement($inputClosePopupDeMerdeSelector)
                    ->click();
            } catch (Exception $e) {
                $this->error('While logging in, tried to intercept cookies relative popup but was unfortunately not here.'); //! Ca va être dur le TOEIC
            } finally {
                //TODO: Virer si pas de traitement supplémentaire
                $bar->advance();
            }

            $driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($loginFormButtonSelector));
            $driver->findElement($loginFormButtonSelector)
                ->click();

            try {
                $driver->wait(5, 1)
                    ->until(WebDriverExpectedCondition::presenceOfElementLocated(
                        $inputUsernameSelector
                    ));
            } catch (Exception $e) {
                $this->error("Form didn't showed up. Aizekyel burnt to death.");
            } finally {
                $bar->advance();
            }

            $driver->action()
                ->sendKeys($driver->findElement($inputUsernameSelector), $username)
                ->sendKeys($driver->findElement($inputPasswordSelector), $password)
                ->perform();
            $driver->findElement($inputSubmitSelector)->click();
        } catch (Exception $e) {
            $this->error("Sa mere bloqué");
            throw $e;
        } finally {
            //! Ne pas changer l'URL parce que là on attend la redirection en fait hein, c'est chiant sinon
            // $driver->get($boursoramaBaseUrl);
            $bar->advance();
            $bar->finish();
            $driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($dataUsernameSelector));

            $dataUsernameElement = $driver->findElement($dataUsernameSelector);
            $dataUsernameString = trim($dataUsernameElement->getDomProperty("innerText"));
            $this->info("\nVous êtes connectés en tant que : {$dataUsernameString}.");
        }
    }
}
