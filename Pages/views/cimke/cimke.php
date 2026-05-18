<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
$cimke = $this->kategoriacimke;
?><div class="normalcontent">
    <h1><?=ucfirst($cimke['cimke_nev'])?></h1><?php
    if($this->irasjog) {

        ?><a class="anchbutton" href="<?= ROOT_PATH . '/cimke/szerkeszt/' . $cimke['slug'] ?>">
            Címke szerkesztése
        </a><?php
    }
?></div>