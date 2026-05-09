<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Settings;

class Recept extends Page
{
    public string $cimke = "recept";
    public ?array $recept = null;
    public array $apimethods = [
        'GET' => ['kereses' => 'ReceptFuzzyList',
            'receptkategoriak' => 'ReceptKategoriak'],
        'POST' => ['ertekel' => 'Ertekel',
            'kedvencel' => 'MentSzakacskonyv']
    ];
    protected string $viewsgyoker = __DIR__ . "/views/recept/";
    protected bool $irasjog = false;
    protected array $jsfiles = [];
    protected FormBuilder $form;
    protected array $views = [
        'recept' => 'recept.php',
        'receptek' => 'receptek.php',
        'szakacskonyv' => 'szakacskonyv.php',
        'szerkeszt' => 'szerkeszt.php',
        'uj' => 'uj.php'
    ];

    public function Router(array $params) : Page {
        $this->validpagemethods = ['uj', 'szerkeszt'];
        $params = $this->ParseGet($params);
        if($this->selectedpage == 'recept') {
            $this->pagepath = 'recept';

            if($params['method'] == 'uj' || $params['method'] == 'szerkeszt') {
                $this->jsfiles[] = 'Pages/views/_assets/js/fileupload.js';
                $this->jsfiles[] = 'Pages/views/recept/assets/listautocomplete.js';
                $this->jsfiles[] = 'includes/external/ckeditor5/ckeditor.js';
                $this->jsfiles[] = 'Pages/views/_assets/js/ckeditorinitializer.js';
                $this->PHPvarsToJS['selectorname'] = 'recept_szoveg';
                $this->PHPvarsToJS['uploadcontainer'] = "kepek";
                if($params['method'] == 'uj')
                    $this->PHPvarsToJS['iterator'] = 0;
            }

            if($params['method'] == 'uj' && Settings::$uid) {
                $this->muvelet = 'uj';
                $this->form = $this->Form('uj');
                $this->view = $this->views['uj'];
                return $this;
            }

            if(!isset($params['elemid']))
                return new Recept('receptek');

            if($params['method'] == 'szerkeszt' && Settings::$uid && $this->GetIrasjog($params['elemid'])) {
                $this->muvelet = 'szerkeszt';
                $this->form = $this->Form('szerkeszt');
                $this->view = $this->views['szerkeszt'];
            }
            else {
                $this->view = $this->views['recept'];
            }
            return $this;
        }

        if($this->selectedpage == 'receptek') {
            $this->pagepath = 'receptek';
            $this->view = $this->views['receptek'];
            return $this;
        }

        if($this->selectedpage == 'szakacskonyv' && Settings::$uid) {
            $this->pagepath = 'szakacskonyv';
            $this->view = $this->views['szakacskonyv'];
            return $this;
        }
        return $this;
    }

    public function HtmlHead() : void {
        $this->title .= ' - ' . ucfirst($this->cimke);
        if ($this->selectedpage == "recept") {
            if ($this->muvelet == "uj") {
                $this->title .= ' - Új recept felvitele';
                $this->sitedesc = "Új recept rögzítése a rendszerbe";
            } elseif($this->recept) {
                if ($this->muvelet == "szerkeszt"){
                    $this->title .= ': ' . ucfirst(Helpers::NeveloHatarozo($this->recept['recept_nev'])) . 'szerkesztése';
                    $this->sitedesc = "Az alapanyag szerkesztése";
                }
                else {
                    $this->title .= ": " . $this->recept['recept_nev'];
                    $this->sitedesc = ucfirst(Helpers::NeveloHatarozo($this->recept['recept_nev'])) . " elkészítésének módja";
                }
                $this->canonical = ROOT_PATH . '/recept/' . $this->recept['slug'];
            }
        }
        $this->ablakcim = $this->title;
        include(__DIR__ . "/views/_assets/htmlheader.php");
    }

    private function Form(string $muvelet, ?array $recept = null) : FormBuilder {
        $form = (new FormBuilder())
            ->TextBox('recept_szoveg', 'Recept szövege')
            ->Number('adagmeret', 'Adag (Hány főre elég?)')
            ->Checkbox('lathatosag', 'Recept látható nyilvánosan');
        if($muvelet == 'szerkeszt' && $recept != null && $this->GetIrasjog($recept['recept_id'])) {
            $form->Hidden('slug')
                ->Hidden('recept_id')
                ->SetValue('slug', $recept['slug'])
                ->SetValue('recept_id', $recept['recept_id'])
                ->SetValue('adagmeret', $recept['adagmeret'])
                ->SetValue('recept_szoveg', $recept['recept_szoveg'])
                ->SetValue('lathatosag', $recept['lathatosag']);
        }
        return $form;
    }

    protected function Uj () : bool
    {
        if(Settings::$uid) {
            $slug = Helpers::SlugGenerator($_POST['recept_nev']);
            $slug = Helpers::SlugVerifier($slug, [ReceptDB::class, 'GetRecept']);
            $alapanyagok = $this->ParseAlapanyagok($_POST['alapanyagok']);
            print_r($alapanyagok);
            $eredmeny = ReceptDB::UjRecept($_POST['recept_nev'], $_POST['recept_szoveg'], $_POST['lathatosag'] ?? 0, $slug, $_POST['adagmeret'], $alapanyagok, Settings::$uid);
            if($eredmeny)
                $this->redirtarget = ROOT_PATH . '/recept/' . $slug;
            return $eredmeny;
        }
        else
            return false;
    }

    protected function Szerkeszt () : bool
    {
        if($this->GetIrasjog($_POST['recept_id'])) {
            $eredmeny = ReceptDB::ReceptSzerkeszt($_POST['alapanyag_nev'], $_POST['kaloria'], $_POST['szenhidrat'] ?? null, $_POST['feherje'] ?? null, $_POST['zsir'] ?? null, $_POST['mertekegyseg'], $cukor, $gluten, $laktoz, $_POST['slug']);
            if($eredmeny)
                $this->redirtarget = ROOT_PATH . '/recept/' . $_POST['slug'];
            return $eredmeny;
        }
        else
            return false;
    }

    private function ParseAlapanyagok(?array $alapanyagok) : array {
        if(!$alapanyagok)
            return [];
        $feldolgozott = [];
        $allergenek = ['cukor' => 0, 'gluten' => 0, 'laktoz' => 0];
        $alapdb = AlapanyagDB::GetAlapanyagok();
        foreach($alapanyagok as $alapanyag) {
            $megtalalt = false;
            $osszetevo = $alapanyag['alapanyag'];

            foreach($alapdb as $alap) {
                if($alap['alapanyag_id'] == $osszetevo) {
                    if($alap['cukor'] == '*')
                        $allergenek['cukor'] = 1;
                    if($alap['glutén'] == '*')
                        $allergenek['gluten'] = 1;
                    if($alap['laktóz'] == '*')
                        $allergenek['laktoz'] = 1;
                    $megtalalt = true;
                    break;
                }
            }
            if($megtalalt) {
                $mertek = $this->ParseMennyisegek($alapanyag['mennyiseg']);
                $feldolgozott[] = ['mennyiseg' => $mertek['mennyiseg'] ?? null, 'mertekegyseg' => $mertek['mertekegyseg'] ?? null, 'alapanyag_id' => $osszetevo];
            }
        }

        return ['alapanyagok' => $feldolgozott, 'allergenek' => $allergenek];
    }

    private function ParseMennyisegek(?string $mennyiseg) : array {
        if(!$mennyiseg)
            return [];
        $tmp = explode(' ', $mennyiseg);
        $feldolgozott = [];
        switch(count($tmp)) {
            case 1:
                if(is_numeric($tmp[0]) && is_int((int)$tmp[0]))
                    $feldolgozott['mennyiseg'] = (int)$tmp[0];
                else
                    $feldolgozott['mennyiseg'] = null;
            case 2:
                if(is_numeric($tmp[0]) && is_int((int)$tmp[0]))
                    $feldolgozott['mennyiseg'] = (int)$tmp[0];
                else
                    $feldolgozott['mennyiseg'] = null;
                $feldolgozott['mertekegyseg'] = $tmp[1];
        }
        return $feldolgozott;
    }

    public function GetIrasjog(int|string|null|bool $elem_id) : bool {
        if(!$elem_id)
            return false;
        if(Settings::$admin)
            return true;

        return ReceptDB::GetReceptIrasjog(Settings::$uid, $elem_id);
    }

    public function MentSzakacskonyv() {
    }

    public function Ertekel() : array|bool {
        if($_SERVER['REQUEST_METHOD'] !== 'POST'
            || !isset($_POST['recept_id'])
            || !isset($_POST['ertekeles'])
        )
            return false;

        if(!Settings::$uid) {
            return ['message' => 'Csak bejelentkezett felhasználók értékelhetnek receptet!',
                'status' => 403];
        }

        if(is_numeric($_POST['ertekeles']) && in_array((int)$_POST['ertekeles'], [1, 2, 3, 4, 5]))
            $ertekeles = (int)$_POST['ertekeles'];
        else
            return ['message' => 'Hibás értékelés! Csak 1-től 5-ig lehet értékelni!',
                'status' => 400];

        return ReceptDB::Ertekeles(Settings::$uid, $_POST['recept_id'], $ertekeles);
    }
}