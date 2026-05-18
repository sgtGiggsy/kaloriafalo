<?php

use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Settings;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Címkék listája</h1><?php
    $cimkek = $this->kategoriacimke;
    if(Settings::$uid) {
        ?><a class="anchbutton" href="<?= ROOT_PATH . '/cimke/uj' ?>">
            Új címke felvitele
        </a><?php
    }
    ?><?=Helpers::RenderArrayAsTable($cimkek, 'cimke', 'slug');?>
    <?=$this->Lapozo()?>
</div>