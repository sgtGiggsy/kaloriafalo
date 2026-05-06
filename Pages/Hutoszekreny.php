<?php

namespace Kaloriafalo\pages;

use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Settings;

class Hutoszekreny extends Page
{
    public string $cimke = "hutoszekreny";

    public array $apiresponse = [];
    public array $apimethods = [
        'GET' => ['tartalom' => 'SajatHuto'],
        'POST' => ['ujtetel' => 'UjTetel']
    ];
    protected ?array $hutoszekreny = null;
    protected string $viewsgyoker = __DIR__ . "/views/hutoszekreny/";
    protected bool $irasjog = false;
    protected FormBuilder $form;
    protected array $views = [
        'hutoszekreny' => 'hutoszekreny.php',
        'hutoszekrenyek' => 'hutoszekrenyek.php',
        'szerkeszt' => 'szerkeszt.php',
        'hutotartalom' => 'hutotartalom.php'
    ];

    public function Router(array $params) : Page {
        if($this->type == 'hutoszekreny') {
            $sajathuto = !Settings::$admin || (Settings::$admin && !isset($params[0])) || (Settings::$admin && $params[0] == 'szerkeszt');
            if($sajathuto) {
                $this->hutoszekreny = $this->SajatHuto();
                $this->irasjog = true;
                if(isset($params[0]) && $params[0] == 'szerkeszt') {
                    $this->HutoSzerkeszt();
                }
                return $this;
            }

            if(isset($params[0]) && $params[0] != 'szerkeszt')
                $this->hutoszekreny = HutoszekrenyDB::GetHutoszekrenyById($params[0]);

            if(!$this->hutoszekreny) {
                return new SinglePage("404");
            }
            elseif(in_array('szerkeszt', $params)) {
                $this->irasjog = true;
                $this->HutoSzerkeszt();
                return $this;
            }
            else {
                $this->irasjog = true;
                return $this;
            }
        }
        elseif($this->type == 'hutoszekrenyek' && Settings::$admin) {
            $this->irasjog = true;
            $this->Hutoszkerenylista();
            return $this;
        }
        else
            return new SinglePage("403");
    }

    public function HtmlHead() : void {
        $this->keywords = array();
        $this->canonical = ROOT_PATH . '/' . $this->pagepath;
        $this->ogtype = "website";
        $this->publishtime = null;
        $this->shareimage = null;
        if ($this->type == "hutoszekrenyek") {
            $this->title .= " - Hűtőszekrények";
            $this->sitedesc = "Itt találhatóak a felhasználók hűtőszekrényei";
        } elseif ($this->type == "hutoszekreny") {
            $this->title .= ' - Hűtőszekrény';
            $this->sitedesc = "Felhasználói hűtőszekrény";
        } elseif ($this->type == "szerkeszt") {
            $this->title .= " - Hűtőszekrény szerkesztése";
            $this->sitedesc = "Hűtőszekrény szerkesztése";
        }
        $this->ablakcim = $this->title;
        include(ROOT_DIR . "/includes/htmlheader.inc.php");
    }

    protected function Szerkeszt() {

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
    private function HutoSzerkeszt() : void {
        $this->type = 'szerkeszt';
        $this->muvelet = 'szerkeszt';
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->form = $this->Form('szerkeszt', $this->hutoszekreny);
            $this->pagepath = 'hutoszekreny/'. $this->hutoszekreny['huto_id'] .'/szerkeszt';
            $this->view = $this->views['szerkeszt'];
        }
    }

    private function Hutoszkerenylista() : void {
        $this->pagepath = 'hutoszekrenyek';
        $this->view = $this->views['hutoszekrenyek'];
        $this->hutoszekreny = HutoszekrenyDB::GetHutoszekrenyek();
    }

    public function GetIrasjog(int|string $huto_id) : bool {
        if(Settings::$admin)
            return true;

        return HutoszekrenyDB::GetHutoIrasjog(Settings::$uid, $huto_id);
    }

    public function GetOlvasasjog(int|string $huto_id) : bool {
        if(Settings::$admin)
            return true;

        return HutoszekrenyDB::GetHutoIrasjog(Settings::$uid, $huto_id);
    }

    public function Hutoszekreny(int|string|null $identifier = null, ) : ?array {
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