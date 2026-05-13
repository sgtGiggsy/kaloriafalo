<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Settings;

class Felhasznalo extends Page
{
    protected array $felhasznalo;
    protected string $viewsgyoker = __DIR__ . "/views/felhasznalo/";
    protected FormBuilder $form;
    protected array $views = [
        'felhasznalo' => 'felhasznalo.php',
        'felhasznalok' => 'felhasznalok.php',
        'profil' => 'felhasznalo.php',
        'szerkesztes' => 'szerkesztes.php'
    ];

    protected array $szintek = [
        1 => 'Felhasználó',
        2 => 'Admin',
        3 => 'Oldal tulajdonos'
    ];

    public function Router(array $params) : Page {
        $this->validpagemethods = ['szerkesztes'];
        $selecteduser = null;
        $params = $this->ParseGet($params);
        if($this->selectedpage == 'felhasznalo' || $this->selectedpage == 'profil') {
            if(!Settings::$uid)
                return new SinglePage(403);

            if(!Settings::$admin || (Settings::$admin && !$params['elemid']))
                $selecteduser = Settings::$uid;
            else
                $selecteduser = $params['elemid'];

            $this->felhasznalo = FelhasznaloDB::GetFelhasznalo($selecteduser);
            if(!$params['method']) {
                $this->view = $this->views['felhasznalo'];
            }

            if($params['method'] == 'szerkesztes') {
                $this->view = $this->views['szerkesztes'];
            }
        } elseif ($this->selectedpage == "felhasznalok") {
            if(!Settings::$admin)
                return new SinglePage(403);

            $this->felhasznalo = FelhasznaloDB::GetFelhasznalok();
            $this->view = $this->views['felhasznalok'];
        }

        return $this;
    }

    public function HtmlHead() : void {
        if ($this->selectedpage == "felhasznalo" || $this->selectedpage == "profil") {
            $this->title .= " - " . $this->felhasznalo['usernev'] . " profilja";
            $this->sitedesc = "Felhasználói profiloldal";
        } elseif ($this->selectedpage == "felhasznalok") {
            $this->title .= ' - Hűtőszekrény';
            $this->sitedesc = "Felhasználói hűtőszekrény";
        }
        $this->ablakcim = $this->title;
        include(__DIR__ . "/views/_assets/htmlheader.php");
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

    protected function Szerkesztes() {

    }
}