<?php

namespace Kaloriafalo\Pages;
use Exception;
use Kaloriafalo\Classes\Settings;

Class Page
{
    public bool $valid = true;
    public ?string $redirtarget = null;
    public ?string $muvelet = null;
    public ?string $mixintext = null;
    public array $apimethods = [];
    public string $type;
    public string $cimke = "";
    protected array $keywords = array();
    protected ?string $canonical = null;
    protected ?string $title;
    protected ?string $ogtype = null;
    protected ?string $publishtime = null;
    protected ?string $shareimage = null;
    protected ?string $ablakcim = null;
    protected ?string $robots = null;
    protected ?string $sitedesc = null;
    protected ?string $pagepath = null;
    protected string $viewsgyoker = ROOT_DIR;
    protected ?string $view;
    protected bool $irasjog = false;
    protected ?string $aloldal = null;
    private string $file;
    protected array $views = [];

    public function __construct(string $type) {
        $this->title = $GLOBALS['ablakcim'];
        $this->type = $type;
        $this->redirtarget = $_SERVER['REQUEST_URI'];
        if (!isset($this->views[$type])) {
            $this->valid = false;
        }
        else
            $this->view = $this->views[$type];
    }

    public function HtmlHead() : void {
        if (function_exists('HtmlHead')) {
            HtmlHead($this->keywords, $this->canonical, $this->title, $this->ogtype, $this->publishtime, $this->shareimage, $this->robots, $this->ablakcim, $this->sitedesc, $this->cimke);;
            include(ROOT_DIR . "/includes/htmlheader.inc.php");
        }
    }

    public function LdJSON() : void {
        
    }

    public function Render() : void {
        if(!isset($this->view))
            return;
        if ($this->aloldal)
            include($this->viewsgyoker . $this->views[$this->aloldal]);
        include($this->viewsgyoker . $this->view);
    }

    public function Post() : bool {
        $fname = ucfirst($this->type);
        return $this->$fname();
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
        return isset($this->views[$this->type]);
    }
}