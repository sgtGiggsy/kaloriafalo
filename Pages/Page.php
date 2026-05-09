<?php

namespace Kaloriafalo\Pages;
use Exception;
use Kaloriafalo\Classes\Settings;

Class Page
{
    public ?string $redirtarget = null;
    public string $selectedpage;
    public ?string $muvelet = null;
    public ?string $mixintext = null;
    public array $apimethods = [];
    public string $cimke = "";
    protected array $keywords = array();
    protected ?string $canonical = null;
    protected ?string $title;
    protected ?string $ogtype = 'website';
    protected ?string $publishtime = null;
    protected ?string $shareimage = null;
    protected ?string $ablakcim = null;
    protected ?string $robots = null;
    protected ?string $sitedesc = null;
    protected ?string $pagepath = null;
    protected array $jsfiles = [];
    protected array $PHPvarsToJS = [];
    protected ?string $localcss = "";
    protected array $validpagemethods = [];
    protected string $viewsgyoker = ROOT_DIR;
    protected ?string $view;
    protected bool $irasjog = false;
    protected ?string $aloldal = null;
    private string $file;
    protected array $views = [];

    public function __construct(string $type) {
        $this->title = $GLOBALS['ablakcim'];
        $this->selectedpage = $type;
        $this->canonical = ROOT_PATH . '/' . $type;
        $this->redirtarget = $_SERVER['REQUEST_URI'];

        if(isset($this->views[$type]))
            $this->view = $this->views[$type];
    }

    public function HtmlHead() : void {
        if (function_exists('HtmlHead')) {
            HtmlHead($this->keywords, $this->canonical, $this->title, $this->ogtype, $this->publishtime, $this->shareimage, $this->robots, $this->ablakcim, $this->sitedesc, $this->cimke);;
            include(__DIR__ . "/views/_assets/htmlheader.php");
        }
    }

    public function LdJSON() : void {
    }

    public function Render() : void {
        if(!file_exists($this->viewsgyoker . $this->view))
            return;

        foreach ($this->PHPvarsToJS as $key => $value) {
            Settings::$PHPvarsToJS[$key] = $value;
        }
        foreach ($this->jsfiles as $file) {
            Settings::$jsfiles[] = $file;
        }

        if($this->aloldal) {
            if(!file_exists($this->viewsgyoker . $this->views[$this->aloldal]))
                return;
            include($this->viewsgyoker . $this->views[$this->aloldal]);
        }
        include($this->viewsgyoker . $this->view);
    }

    public function Post() : bool {
        $fname = ucfirst($this->muvelet);
        if(!method_exists($this, $fname))
            return false;
        return $this->$fname();
    }

    protected function ParseGet(?array $params) : array {
        if(!$params || count($params) == 0)
            return ['elemid' => null, 'method' => null];

        switch(count($params)) {
            case 1:
                if(is_numeric($params[0]))
                    return ['elemid' => (int)$params[0], 'method' => null];
                if(in_array($params[0], $this->validpagemethods))
                    return ['method' => $params[0], 'elemid' => null];
                return ['elemid' => $params[0], 'method' => null];
            case 2:
                if(is_numeric($params[1]))
                    return ['elemid' => (int)$params[1], 'method' => $params[0]];
                return ['elemid' => $params[1], 'method' => $params[0]];
            default:
                return ['elemid' => null, 'method' => null];
        }
    }

    public function Router(array $params) : Page {
        return $this;
    }

    public function GetIrasjog(int|string $elem_id) : bool {
        if(Settings::$admin)
            return true;
        return false;
    }

    public function GetOlvasasjog(int|string $elem_id) : bool {
        if(Settings::$admin)
            return true;
        return false;
    }

    public function ValidView() : bool {
        return isset($this->views[$this->selectedpage]);
    }
}