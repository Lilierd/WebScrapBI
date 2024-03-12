<?php
require 'vendor/autoload.php';

use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;

$mink = new Mink(
    array(
        'browser' => new Session(new ChromeDriver('http://localhost:1234', null, 'http://www.google.com', ['downloadBehavior' => 'allow', 'downloadPath' => __DIR__]))
    )
);

$mink->setDefaultSessionName('browser');
$session = $mink->getSession();

$session->visit("https://www.boursorama.com/bourse/");

$page = $session->getPage();

//aria-label="Ouvrir la barre de recherche"
$boutonRes = $page->find("css","button.c-navigation__header-link-media");
$boutonRes->click();

$resBar = $page->find("css","input.topbar-searchbar__input-field");
$resBar->setValue("Microsoft");

$cookie = $page->find("css","button#didomi-notice-agree-button");   // <----- Important de faire des trucs avant pour que ça marche
if($cookie != null)
    $cookie->click();

$resBar->keyPress("\u10");

sleep(10);

/* $element = $page->find("css", "#ctl00_MainContent_lbCommodity");
$element->setValue(107);    //Choix de la commodity
$element = $page->find("css", "#ctl00_MainContent_lbCountry");
$element->setValue("0:0");  //Choix du pays
$element = $page->find("css", "#ctl00_MainContent_rblOutputType_0");
$element->setValue(2);  //Choix de Excel
$bouton = $page->find("css", "#ctl00_MainContent_btnSubmit");
$bouton->click();   //Clic pour DL

$f = "ExportSalesDataByCommodity.xls";
$essai = 1;
while (!file_exists($f) && $essai < 10) {
    sleep(1);
    $essai++;
}

$pays = "AUSTRALIE";

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
$ss = $reader->load($f);
if ($ss) {
    $ws = $ss->getActiveSheet();
    for ($i = 8; $i < $ws->getHighestDataRow(); $i++) {
        if($pays == $ws->getCell("E".$i)->getValue()){
            echo $ws->getCell("F".$i)->getValue()."\n";
            break;
        }
    }
} else {
    echo "Erreur à l'ouverture du fichier Excel";
} */
//echo $element->getText();

//system("pause");
