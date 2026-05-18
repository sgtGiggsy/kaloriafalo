<?php

namespace Kaloriafalo\pages;

use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Settings;

class Hutoszekreny extends Page
{
    public string $cimke = "hűtőszekrény";
    public array $apimethods = [
        'GET' => ['tartalom' => 'SajatHuto'],
        'POST' => ['ujtetel' => 'UjTetel']
    ];
    protected ?array $hutoszekreny = null;
    protected string $viewsgyoker = __DIR__ . "/views/hutoszekreny/";
    protected array $elerhetoreceptek;
    protected array $jsfiles = ['Pages/views/hutoszekreny/assets/listautocomplete.js'];
    protected FormBuilder $form;
    protected array $views = [
        'hutoszekreny' => 'hutoszekreny.php',
        'hutoszekrenyek' => 'hutoszekrenyek.php',
        'szerkeszt' => 'szerkeszt.php'
    ];

    public function Router(array $params) : Page {
        $this->validpagemethods = ['szerkeszt', 'tartalomszerkeszt'];
        $params = $this->ParseGet($params);
        if($this->selectedpage == 'hutoszekreny') {
            if(!Settings::$uid)
                return new SinglePage('401');

            $sajathuto = !Settings::$admin || (Settings::$admin && !$params['elemid']);

            if($sajathuto) {
                $this->hutoszekreny = $this->SajatHuto();
                $this->elerhetoreceptek = HutoszekrenyDB::ReceptlistByTartalom($this->hutoszekreny['huto']['huto_id']);
                $this->irasjog = true;

                if($params['method'] == 'szerkeszt') {
                    $this->muvelet = 'szerkeszt';
                    $this->HutoSzerkeszt();
                }
                elseif($params['method'] == 'tartalomszerkeszt') {
                    $this->muvelet = 'tartalomszerkeszt';
                }
                return $this;
            }

            if(!$params['elemid'])
                return new SinglePage("404");

            if(!is_numeric($params['elemid']))
                return new SinglePage("404");

            if(!$this->Getolvasasjog($params['elemid']))
                return new SinglePage("403");

            $this->hutoszekreny = $this->HutoszekrenyById($params['elemid']);
            if(!$this->hutoszekreny)
                return new SinglePage("404");

            $this->irasjog = true;
            $this->elerhetoreceptek = HutoszekrenyDB::ReceptlistByTartalom($params['elemid']);
            if($params['method'] == 'szerkeszt') {
                $this->muvelet = 'szerkeszt';
                $this->HutoSzerkeszt();
            }

            if($params['method'] == 'tartalomszerkeszt') {
                $this->muvelet = 'tartalomszerkeszt';
            }

            return $this;
        }
        elseif($this->selectedpage == 'hutoszekrenyek' && Settings::$admin) {
            $this->view = $this->views['hutoszekrenyek'];
            $this->irasjog = true;
            $startindex = 0;
            $elemperoldal = 20;
            $this->LapozasPrepare($startindex, $elemperoldal, $params);
            $this->hutoszekreny = $this->LapozasFinalize(HutoszekrenyDB::GetHutoszekrenyek($startindex, $elemperoldal), $elemperoldal);
            return $this;
        }
        else
            return new SinglePage("403");
    }

    public function HtmlHead() : void {
        if ($this->selectedpage == "hutoszekrenyek") {
            $this->title .= " - Hűtőszekrények";
            $this->sitedesc = "Itt találhatóak a felhasználók hűtőszekrényei";
        } elseif ($this->selectedpage == "hutoszekreny") {
            $this->title .= ' - Hűtőszekrény';
            $this->sitedesc = "Felhasználói hűtőszekrény";
        } elseif ($this->selectedpage == "szerkeszt") {
            $this->title .= " - Hűtőszekrény szerkesztése";
            $this->sitedesc = "Hűtőszekrény szerkesztése";
        }
        $this->ablakcim = $this->title;
        include(__DIR__ . "/views/_assets/htmlheader.php");
    }

    protected function Szerkeszt() : bool {
        if(!isset($_POST['huto_id']) || !$this->GetIrasjog($_POST['huto_id']))
            return false;
        $eredmeny = HutoszekrenyDB::Szerkeszt($_POST['huto_id'], $_POST['huto_nev'] ?? null);
        if($eredmeny)
            $this->redirtarget = ROOT_PATH . '/hutoszekreny/' . $_POST['huto_id'];
        return $eredmeny;
    }

    protected function Tartalomszerkeszt() : bool {
        if(!isset($_POST['huto_id']) || !$this->GetIrasjog($_POST['huto_id']))
            return false;

        $eredmeny = HutoszekrenyDB::TartalomSzerkeszt($_POST['huto_id'], $_POST['alapanyagok'] ?? null);
        if($eredmeny) {
            $this->redirtarget = ROOT_PATH . '/hutoszekreny/' . $_POST['huto_id'];
            $this->mixintext = 'A hűtőszekrény tartalmának frissítése sikeres';
        }
        else
            $this->mixintext = 'A hűtőszekrény tartalmának frissítése hibába ütközött';
        return $eredmeny;
    }

    protected function Form(string $type, ?array $hutoszekreny = null) : FormBuilder {
        $form = (new FormBuilder())
            ->Text('huto_nev', 'Hűtőszekrény név', true);
        if($type == 'szerkeszt') {
            $form->Hidden('huto_id')
                ->SetValue('huto_id', $hutoszekreny['huto_id'])
                ->SetValue('huto_nev', $hutoszekreny['huto_nev']);
        }
        return $form;
    }

    public function SajatHuto() : array {
        return HutoszekrenyDB::GetHutoszekrenyByUser(Settings::$uid);
    }

    private function HutoszekrenyById(int $huto_id) : array {
        return HutoszekrenyDB::GetHutoszekrenyById($huto_id);
    }

    private function HutoSzerkeszt() : void {
        $this->hutoszekreny = $this->hutoszekreny['huto'];
        $this->muvelet = 'szerkeszt';
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->form = $this->Form('szerkeszt', $this->hutoszekreny);
            $this->view = $this->views['szerkeszt'];
        }
    }

    public function GetIrasjog(int|string|null|bool $elem_id) : bool {
        if(!$elem_id)
            return false;
        if(Settings::$admin)
            return true;

        return HutoszekrenyDB::GetHutoIrasjog(Settings::$uid, $elem_id);
    }

    public function GetOlvasasjog(int|string|null|bool $elem_id) : bool {
        if(!$elem_id)
            return false;

        if(Settings::$admin)
            return true;

        return HutoszekrenyDB::GetHutoIrasjog(Settings::$uid, $elem_id);
    }

    public function Hutoszekreny(int|string|null $identifier = null) : ?array {
        if($identifier === null)
            return null;

        if(is_int($identifier)) {
            $result = HutoszekrenyDB::GetHutoszekrenyById($identifier);
        }
        else {
            $result = HutoszekrenyDB::GetHutoszekrenyByNev($identifier);
        }
        if(count($result) == 0)
            return null;
        else
            return $result;
    }
    public function Hutoszekrenyek() : ?array {
        if(!Settings::$admin)
            return null;

        $result = HutoszekrenyDB::GetHutoszekrenyek();
        if(count($result) == 0)
            return null;
        return $result;
    }
}