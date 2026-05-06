<?php

namespace Kaloriafalo\Pages;

class SinglePage extends Page
{
    private string $file;
    protected array $views = [
        '403' => '403.php',
        '404' => '404.php',
        'fooldal' => 'fooldal.php',
        'adatkezelesi-tajekoztato' => 'adatkezelesi-tajekoztato.php',
        'security' => 'security.php',
        'impresszum' => 'impresszum.php',
        'media-ajanlat' => 'media-ajanlat.php',
        'kapcsolat' => 'kapcsolat.php'
    ];

    public function __construct(string $type) {
        $this->title = $GLOBALS['ablakcim'];
        $this->file = $this->views[$type] ?? $this->views['404'];
        $this->type = $type;
        include(ROOT_DIR . "/" . $this->file);
    }

    public function HtmlHead() : void {
        if (function_exists('HtmlHead')) {
            HtmlHead($this->keywords, $this->canonical, $this->title, $this->ogtype, $this->publishtime, $this->shareimage, $this->robots, $this->ablakcim, $this->sitedesc, $this->cimke);;
            include(ROOT_DIR . "/includes/htmlheader.inc.php");
        }
    }

    public function Render() : void {
        if(function_exists('Render'))
            Render();
    }
}