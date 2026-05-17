<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\FormBuilder;
use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Settings;
class Cimke extends Page
{
    public string $cimke = "cimke";
    protected ?array $kategoriacimke = null;
    protected string $viewsgyoker = __DIR__ . "/views/cimke/";
    protected FormBuilder $form;
    protected array $views = [
        'cimkek' => 'cimkek.php',
        'cimke' => 'cimke.php',
        'uj' => 'uj.php',
        'szerkeszt' => 'szerkeszt.php'
    ];

    public function Router(array $params) : Page {
        $this->validpagemethods = ['uj', 'szerkeszt', 'oldal'];
        $params = $this->ParseGet($params);
        if(!Settings::$admin) {
            return new SinglePage(403);
        }

        if($this->selectedpage == 'cimke') {
            if($params['method'] == 'uj') {
                $this->muvelet = 'uj';
                if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $this->view = $this->views['uj'];
                    $this->form = $this->Form('uj');
                }
                return $this;
            }

            if(!isset($params['elemid'])) {
                return (new Cimke('cimkek'))->Router($params);
            }

            if($params['method'] == 'szerkeszt') {
                $this->muvelet = 'szerkeszt';
                if($_SERVER['REQUEST_METHOD'] === 'POST')
                    return $this;
            }

            $kategoriacimke = CimkeDB::GetCimke($params['elemid']);
            if(!$kategoriacimke) {
                return new SinglePage('404');
            }
            else {
                $this->irasjog = true;
                $this->kategoriacimke = $kategoriacimke;
                $this->view = $this->views['cimke'];
            }

            if($params['method'] == 'szerkeszt') {
                $this->form = $this->Form('szerkeszt', $kategoriacimke);
                $this->view = $this->views['szerkeszt'];
            }
        }
        elseif($this->selectedpage == 'cimkek') {
            $this->view = $this->views['cimkek'];
            $startindex = 0;
            $elemperoldal = 20;
            $this->LapozasPrepare($startindex, $elemperoldal, $params);
            $this->kategoriacimke = $this->LapozasFinalize(CimkeDB::GetCimkek($startindex, $elemperoldal), $elemperoldal);
        }

        return $this;
    }

    public function HtmlHead() : void {
        $this->title .= ' - ' . ucfirst($this->cimke);
        if ($this->selectedpage == "cimkek") {
            $this->sitedesc = "Itt található az elérhető cimkek listája";
        } elseif ($this->selectedpage == "cimke") {
            if ($this->muvelet == "uj") {
                $this->title .= ' - Új cimke felvitele';
                $this->sitedesc = "Új cimke felvitele";
            } elseif ($this->muvelet == "szerkeszt") {
                $this->title .= ': ' . ucfirst($this->kategoriacimke['cimke_nev']);
                $this->sitedesc = "A cimke szerkesztése";
            }
            else {
                $this->title .= ' - ' . ucfirst($this->kategoriacimke['cimke_nev']);
                $this->sitedesc = "A cimke adatlapja és a hozzá tartozó receptek listája";
            }
        }
        $this->ablakcim = $this->title;
        include(__DIR__ . "/views/_assets/htmlheader.php");
    }

    protected function Uj () : bool
    {
        if(Settings::$admin) {
            $slug = Helpers::SlugGenerator($_POST['cimke_nev']);
            $slug = Helpers::SlugVerifier($slug, [CimkeDB::class, 'GetCimke']);
            $eredmeny = CimkeDB::UjCimke($_POST['cimke_nev'], $slug);
            if($eredmeny)
                $this->redirtarget = ROOT_PATH . '/cimke/' . $slug;
            return $eredmeny;
        }
        else
            return false;
    }

    protected function Szerkeszt () : bool
    {
        if(Settings::$admin) {
            $slug = Helpers::SlugGenerator($_POST['ujslug']);
            if($slug != $_POST['slug'])
                $ujslug = Helpers::SlugVerifier($slug, [CimkeDB::class, 'GetCimke']);
            else
                $ujslug = null;

            $eredmeny = CimkeDB::CimkeSzerkeszt($_POST['cimke_nev'], $_POST['slug'], $ujslug);
            if($eredmeny)
                $this->redirtarget = ROOT_PATH . '/cimke/' . $_POST['slug'];
            return $eredmeny;
        }
        else
            return false;
    }

    public function Form(string $type, ?array $kategoriacimke = null) : FormBuilder {
        $form = (new FormBuilder())
            ->Text('cimke_nev', 'Cimke neve', true);
        if($type == 'szerkeszt') {
            $form->Hidden('slug')
                ->Text('ujslug', 'Kategória azonosítója')
                ->SetValue('slug', $kategoriacimke['slug'])
                ->SetValue('ujslug', $kategoriacimke['slug'])
                ->SetValue('cimke_nev', $kategoriacimke['cimke_nev']);
        }
        return $form;
    }
}