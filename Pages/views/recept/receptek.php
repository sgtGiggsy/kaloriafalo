<?php

use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Settings;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Receptek</h1><?php
    $this->recept;
    if(Settings::$uid) {
        ?><a href="<?= ROOT_PATH . '/recept/uj' ?>">
            Új recept felvitele
        </a><?php
        ?><?=Helpers::RenderArrayAsTable($this->recept, 'recept', 'slug');
    }
?></div>