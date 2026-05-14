<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\FeltoltesHandler;
use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Settings;

class Recept extends Page
{
    public string $cimke = "recept";
    public ?array $recept = null;
    public string $keresesplaceholder = "Mit eszünk ma?";
    public array $apimethods = [
        'GET' => ['kereses' => 'ReceptFuzzyList',
            'receptkategoriak' => 'ReceptKategoriak'],
        'POST' => ['ertekel' => 'Ertekel',
            'bookmark' => 'Bookmark']
    ];
    protected string $viewsgyoker = __DIR__ . "/views/recept/";
    protected bool $irasjog = false;

    protected FormBuilder $form;
    protected array $headerviews = [
        'recept' => 'receptheader.php'
    ];
    protected array $views = [
        'recept' => 'recept.php',
        'receptek' => 'receptek.php',
        'szakacskonyv' => 'szakacskonyv.php',
        'szerkeszt' => 'szerkeszt.php',
        'uj' => 'uj.php'
    ];

    protected array $mediatypes = ['image/jpeg', 'image/bmp', 'image/png', 'image/webp'];

    public function Router(array $params) : Page {
        $this->validpagemethods = ['uj', 'szerkeszt', 'kereses'];
        $params = $this->ParseGet($params);
        if($this->selectedpage == 'receptek' || $this->selectedpage == 'recept' || $this->selectedpage == 'szakacskonyv') {
            $this->jsfiles[] = 'Pages/views/recept/assets/quickactions.js';
        }
        if($this->selectedpage == 'recept') {

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

            $this->recept = ReceptDB::GetRecept($params['elemid']);
            $this->irasjog = $this->GetIrasjog($this->recept['recept']['recept_id']);
            if($params['method'] == 'szerkeszt' && Settings::$uid && $this->irasjog) {
                $this->muvelet = 'szerkeszt';
                $this->form = $this->Form('szerkeszt');
                $this->view = $this->views['szerkeszt'];
            }
            elseif(!$params['method'] == 'szerkeszt' && !$this->GetOlvasasjog($params['elemid']))
                return new SinglePage('403');
            else {
                $this->view = $this->views['recept'];
                $this->headerview = $this->headerviews['recept'];
            }

            return $this;
        }

        if($this->selectedpage == 'receptek') {
            $this->jsfiles[] = 'Pages/views/recept/assets/kereses.js';
            $kereses = null;
            if(isset($params['method']) && isset($params['elemid']) && $params['method'] == 'kereses')
                $kereses = $params['elemid'];

            if(!$kereses) {
                $elemperoldal = 20;
                $startindex = 0;
                if($params['method'] == 'oldal' && isset($params['elemid'])){
                    $this->lapozas['elozo'] = ($params['elemid'] > 1) ? $params['elemid'] - 1 : null;
                    $this->lapozas['kovetkezo'] = $params['elemid'] + 1;
                    $startindex = $params['elemid'] * $elemperoldal;
                }

                $this->recept = ReceptDB::GetReceptek($startindex, $elemperoldal);
                if(!$this->recept) {
                    $this->recept = [];
                }

                if(count($this->recept) < $elemperoldal) {
                    $this->lapozas['kovetkezo'] = null;
                }
            }
            else {
                $this->recept = $this->ReceptKereses($kereses);
                $this->keresesplaceholder = $kereses;
            }
            $this->view = $this->views['receptek'];
            return $this;
        }

        if($this->selectedpage == 'szakacskonyv' && Settings::$uid) {
            $this->recept = ReceptDB::GetSzakacskonyv(Settings::$uid);
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
            } else {
                $recept = $this->recept['recept'];
                if ($this->muvelet == "szerkeszt"){
                    $this->title .= ': ' . ucfirst(Helpers::NeveloHatarozo($recept['recept_nev'])) . 'szerkesztése';
                    $this->sitedesc = "Az alapanyag szerkesztése";
                }
                else {
                    $this->title .= ": " . $recept['recept_nev'];
                    $this->sitedesc = ucfirst(Helpers::NeveloHatarozo($recept['recept_nev'])) . " elkészítésének módja";
                }
                $this->canonical = ROOT_PATH . '/recept/' . $recept['slug'];
            }
        }
        $this->ablakcim = $this->title;
        include(__DIR__ . "/views/_assets/htmlheader.php");
    }

    public function LdJSON() : void {
        if(!($this->selectedpage == 'recept' && !$this->muvelet))
            return;
        $recept = $this->recept['recept'];
        $alapanyagok = $this->recept['alapanyagok'];
        $kepek = $this->recept['kepek'];
        $receptkepek = [];
        $osszetevok = [];
        foreach($alapanyagok as $alapanyag) {
            $mennyiseg = ($alapanyag['mennyiseg']) ? $alapanyag['mennyiseg'] . ' ' : '';
            $mertekegyseg = ($alapanyag['mertekegyseg']) ? $alapanyag['mertekegyseg'] . ' ' : '';
            $osszetevok[] = $mennyiseg . $mertekegyseg . $alapanyag['alapanyag_nev'];
        }
        foreach($kepek as $kep) {
            $receptkepek[] = ROOT_PATH . '/' . $kep['fajl'];
        }
        ?><script type="application/ld+json">
        {
            "@context": "https://schema.org",
          "@type": "Recipe",
          "name": "<?=$recept['recept_nev']?>",
          "description": "<?=$recept['recept_nev']?>",
          <?php if($recept['ertekelesek_szama'] > 0) { ?>
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "<?=$recept['ertekeles']?>",
                "ratingCount": "<?=$recept['ertekelesek_szama']?>"
              },
          <?php } ?>
        
          "recipeIngredient": [
            <?='"' . implode('",
            "', $osszetevok) . '"'?>
        ],
        
          "recipeYield": "<?=$recept['adagmeret']?> adag",
        
          "prepTime": "PT<?=$recept['elokeszuletek']?>M",
          "cookTime": "PT<?=$recept['sutesido']?>M",
          "totalTime": "PT<?=$recept['elokeszuletek'] + $recept['sutesido']?>M",
        
          "datePublished": "<?=Helpers::SQLTimeStampToDate($recept['letrehozas_ideje'])?>",
        
          "image": [
              <?='"' . implode('",
              "', $receptkepek) . '"'?>
        ],
        
          "recipeCategory": "Főétel"
        }
        </script><?php
    }

    private function Form(string $muvelet, ?array $recept = null) : FormBuilder {
        $form = (new FormBuilder())
            ->TextBox('recept_szoveg', 'Recept szövege')
            ->Number('adagmeret', 'Adag (Hány főre elég?)')
            ->Number('elokeszuletek', 'Mennyi ideig tart a főzés/sütés előkészítse?')
            ->Number('sutesido', 'Mennyi a főzés/sütés ideje?')
            ->Checkbox('lathatosag', 'Recept látható nyilvánosan');
        if($muvelet == 'szerkeszt' && $recept != null && $this->GetIrasjog($recept['recept_id'])) {
            $form->Hidden('slug')
                ->Hidden('recept_id')
                ->SetValue('slug', $recept['slug'])
                ->SetValue('recept_id', $recept['recept_id'])
                ->SetValue('adagmeret', $recept['adagmeret'])
                ->SetValue('recept_szoveg', $recept['recept_szoveg'])
                ->SetValue('lathatosag', $recept['lathatosag'])
                ->SetValue('elokeszuletek', $recept['elokeszuletek'])
                ->SetValue('sutesido', $recept['sutesido']);
        }
        return $form;
    }

    protected function Uj () : bool
    {
        if(Settings::$uid) {
            $kepidk = null;
            $slug = Helpers::SlugGenerator($_POST['recept_nev']);
            $slug = Helpers::SlugVerifier($slug, [ReceptDB::class, 'GetRecept']);
            $alapanyagok = $this->ParseAlapanyagok($_POST['alapanyagok']);
            $eredmeny = ReceptDB::UjRecept($_POST['recept_nev'], $_POST['recept_szoveg'], $_POST['lathatosag'] ?? 0, $slug, $_POST['adagmeret'], $alapanyagok, $_POST['elokeszuletek'] ?? null, $_POST['sutesido'] ?? null, Settings::$uid);
            if($eredmeny) {
                $this->redirtarget = ROOT_PATH . '/recept/' . $slug;

                if (isset($_FILES['kepek'])) {
                    $kepek = new FeltoltesHandler($this->mediatypes, 'receptkepek', date('Y'), 'receptkepek', Settings::$uid);
                    $kepidk = $kepek->Feltoltes($_FILES['kepek']);
                }
                if($kepidk['eredmeny']) {
                    $eredmeny = ReceptDB::ReceptKepek($eredmeny, $kepidk['uploadids']);
                }
            }

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

    public function GetOlvasasjog(int|string $elem_id) : bool {
        if(Settings::$admin)
            return true;
        if($this->recept['recept']['lathatosag'] == 1)
            return true;
        if($this->recept['recept']['felhasznalo_id'] == Settings::$uid)
            return true;
        return false;
    }

    private function ReceptKereses(string $needle) : array {
        $searcharr = Helpers::FuzzySearchStringGen($needle);

        $firstpass = ReceptDB::GetReceptekFuzzy($searcharr, 'recept_id');
        if(count($firstpass) == 0)
            return [];

        return Helpers::FuzzySearch($firstpass, $needle, 'recept_nev');
    }

    public function Bookmark() : array|bool {
        if(!isset($_POST['recept_id']))
            return false;

        if(!Settings::$uid)
            return false;

        $eredmeny = ReceptDB::BookmarkRecept($_POST['recept_id'], Settings::$uid);
        if($eredmeny === null) {
            $data['eredmeny'] = true;
            $data['message'] = 'A recept eltávolításra került a Szakácskönyvedből!';
        }
        else {
            $data['eredmeny'] = $eredmeny;
            $data['message'] = $eredmeny ? 'A recept mentése sikeres volt' : 'A recept mentése nem sikerült!';
        }

        return $data;
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

        $ertekeles = ReceptDB::Ertekeles(Settings::$uid, $_POST['recept_id'], $ertekeles);
        $data['eredmeny'] = $ertekeles;
        $data['message'] = $ertekeles ? 'Az értékelésed sikeresen hozzáadva' : 'A receptet nem sikerült értékelni!';

        return $data;
    }
}