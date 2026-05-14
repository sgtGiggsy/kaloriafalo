<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\Controller;
use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Logging;
use Kaloriafalo\Classes\MailHandler;
use Kaloriafalo\Classes\Settings;
class Alapanyag extends Page
{
    public string $cimke = "alapanyag";
    protected ?array $alapanyag = null;
    protected string $viewsgyoker = __DIR__ . "/views/alapanyag/";
    protected FormBuilder $form;
    public array $apimethods = [
        'GET' => ['kereses' => 'AlapanyagFuzzyList']
    ];
    protected array $views = [
        'alapanyagok' => 'alapanyagok.php',
        'alapanyag' => 'alapanyag.php',
        'uj' => 'uj.php',
        'szerkeszt' => 'szerkeszt.php'
    ];

    private static array $mertekegysegek = [
        '' => '',
        'g' => 'gramm',
        'dkg' => 'dekagramm',
        'kg' => 'kilogramm',
        'ml' => 'milliliter',
        'dl' => 'deciliter',
        'l' => 'liter',
        'kk' => 'kávéskanál',
        'tk' => 'teáskanál',
        'ek' => 'evőkanál'
    ];

    public function Router(array $params) : Page {
        $this->validpagemethods = ['uj', 'szerkeszt', 'oldal'];
        $params = $this->ParseGet($params);
        if($this->selectedpage == 'alapanyag') {
            if($params['method'] == 'uj' && Settings::$uid) {
                $this->muvelet = 'uj';
                if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->view = $this->views['uj'];
                    $this->form = $this->Form('uj');
                }
                return $this;
            }

            if(!isset($params['elemid'])) {
                return new Alapanyag('alapanyagok');
            }

            if($params['method'] == 'szerkeszt') {
                $this->muvelet = 'szerkeszt';
                if($_SERVER['REQUEST_METHOD'] === 'POST' && Settings::$admin)
                    return $this;
            }

            $alapanyag = AlapanyagDB::GetAlapanyag($params['elemid']);
            if(!$alapanyag) {
                return new SinglePage('404');
            }
            else {
                $this->irasjog = Settings::$admin;
                $this->alapanyag = $alapanyag;
                $this->view = $this->views['alapanyag'];
            }

            if($params['method'] == 'szerkeszt') {
                if(!$this->irasjog) {
                    return new SinglePage('403');
                }
                else {
                    $this->form = $this->Form('szerkeszt', $alapanyag);
                    $this->view = $this->views['szerkeszt'];
                }
            }
        }
        elseif($this->selectedpage == 'alapanyagok') {
            $this->view = $this->views['alapanyagok'];
            $startindex = 0;
            $elemperoldal = 20;
            $this->LapozasPrepare($startindex, $elemperoldal, $params);
            $this->alapanyag = $this->LapozasFinalize(AlapanyagDB::GetAlapanyagok($startindex, $elemperoldal), $elemperoldal);
        }

        return $this;
    }

    public function HtmlHead() : void {
        $this->title .= ' - ' . ucfirst($this->cimke);
        if ($this->selectedpage == "alapanyagok") {
            $this->sitedesc = "Itt található az elérhető alapanyagok listája";
        } elseif ($this->selectedpage == "alapanyag") {
            if ($this->muvelet == "uj") {
                $this->title .= ' - Új felvitele';
                $this->sitedesc = "Új alapanyag felvitele";
            } elseif ($this->muvelet == "szerkeszt") {
                $this->title .= ': ' . ucfirst($this->alapanyag['alapanyag_nev']);
                $this->sitedesc = "Az alapanyag szerkesztése";
            }
            else {
                $this->title .= ' - ' . ucfirst($this->alapanyag['alapanyag_nev']);
                $this->sitedesc = "Az alapanyag adatlapja és a hozzá tartozó receptek listája";
            }
        }
        $this->ablakcim = $this->title;
        include(__DIR__ . "/views/_assets/htmlheader.php");
    }

    public function Post() : bool {
        @$cukor = (int) $_POST['cukor'] ?? null;
        @$gluten = (int) $_POST['gluten'] ?? null;
        @$laktoz  = (int) $_POST['laktoz'] ?? null;
        $fname = ucfirst($this->muvelet);
        return $this->$fname($cukor, $gluten, $laktoz);
    }

    public function AlapanyagFuzzyList(?string $alapanyag_nev = null) : array|bool {
        if(!$alapanyag_nev)
            return false;

        $searcharr = Helpers::FuzzySearchStringGen($alapanyag_nev);

        $firstpass = AlapanyagDB::GetAlapanyagokFuzzyList($searcharr, 'alapanyag_id');
        if(count($firstpass) == 0)
            return [];

        return Helpers::FuzzySearch($firstpass, $alapanyag_nev, 'alapanyag');
    }

    protected function Uj (int $cukor, int $gluten, int $laktoz) : bool
    {
        if(Settings::$uid) {
            $slug = Helpers::SlugGenerator($_POST['alapanyag_nev']);
            $slug = Helpers::SlugVerifier($slug, [AlapanyagDB::class, 'GetAlapanyag']);
            $eredmeny = AlapanyagDB::UjAlapanyag($_POST['alapanyag_nev'], $slug, $_POST['kaloria'], $_POST['szenhidrat'] ?? null, $_POST['feherje'] ?? null, $_POST['zsir'] ?? null, $_POST['mertekegyseg'], Settings::$uid, $cukor, $gluten, $laktoz);
            if($eredmeny)
                $this->redirtarget = ROOT_PATH . '/alapanyag/' . $slug;
            return $eredmeny;
        }
        else
            return false;
    }

    protected function Szerkeszt (int $cukor, int $gluten, int $laktoz) : bool
    {
        if(Settings::$admin) {
            $eredmeny = AlapanyagDB::AlapanyagSzerkeszt($_POST['alapanyag_nev'], $_POST['kaloria'], $_POST['szenhidrat'] ?? null, $_POST['feherje'] ?? null, $_POST['zsir'] ?? null, $_POST['mertekegyseg'], $cukor, $gluten, $laktoz, $_POST['slug']);
            if($eredmeny)
                $this->redirtarget = ROOT_PATH . '/alapanyag/' . $_POST['slug'];
            return $eredmeny;
        }
        else
            return false;
    }

    public function Form(string $type, ?array $alapanyag = null) : FormBuilder {
        $form = (new FormBuilder())
            ->Text('alapanyag_nev', 'Alapanyag név', true)
            ->Number('kaloria', 'Kalória / 100 mértékegység', true)
            ->Number('szenhidrat', 'Szénhidrát / 100 mértékegység', true)
            ->Number('feherje', 'Fehérje / 100 mértékegység', true)
            ->Number('zsir', 'Zsír / 100 mértékegység', true)
            ->Select('mertekegyseg', 'Mértékegység', self::$mertekegysegek, true)
            ->Checkbox('cukor', 'Cukor')
            ->Checkbox('gluten', 'Glutén')
            ->Checkbox('laktoz', 'Laktóz');
        if($type == 'szerkeszt') {
            $form->Hidden('slug')
                ->SetValue('slug', $alapanyag['slug'])
                ->SetValue('alapanyag_nev', $alapanyag['alapanyag_nev'])
                ->SetValue('kaloria', $alapanyag['kaloria'])
                ->SetValue('szenhidrat', $alapanyag['szenhidrat'])
                ->SetValue('zsir', $alapanyag['zsir'])
                ->SetValue('feherje', $alapanyag['feherje'])
                ->SetValue('mertekegyseg', $alapanyag['mertekegyseg'])
                ->SetValue('cukor', $alapanyag['cukor'])
                ->SetValue('gluten', $alapanyag['gluten'])
                ->SetValue('laktoz', $alapanyag['laktoz'])
                ->SetValue('alapanyag_id', $alapanyag['alapanyag_id']);
        }
        return $form;
    }
}