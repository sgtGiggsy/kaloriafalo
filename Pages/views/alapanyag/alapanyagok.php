<?php

use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Settings;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Alapanyagok listája</h1><?php
    $alapanyag = $this->alapanyag;
    if(Settings::$uid) {
        ?><a class="anchbutton" href="<?= ROOT_PATH . '/alapanyag/uj' ?>">
            Új alapanyag felvitele
        </a><?php
    }
    ?><?=Helpers::RenderArrayAsTable($this->alapanyag, 'alapanyag', 'slug');?>
    <?=$this->Lapozo()?>
</div>