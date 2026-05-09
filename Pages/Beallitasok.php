<?php

namespace Kaloriafalo\Pages;
use Kaloriafalo\Classes\MySQLHandler;

class Beallitasok extends Page
{
    public function HtmlHead() : void {
        //HtmlHead($this->keywords, $this->canonical, $this->title, $this->ogtype, $this->publishtime, $this->shareimage, $this->robots, $this->ablakcim, $this->sitedesc, $this->cimke);
        include(ROOT_DIR . "/includes/htmlheader.php");
    }

    public function Post() : bool {
        return true;
    }
}