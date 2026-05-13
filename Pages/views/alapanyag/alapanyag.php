<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=ucfirst($this->alapanyag['alapanyag_nev'])?></h1><?php
    if($this->irasjog) {
        $alapanyag = $this->alapanyag;
        ?><a class="anchbutton" href="<?= ROOT_PATH . '/alapanyag/szerkeszt/' . $alapanyag['slug'] ?>">
            Alapanyag szerkesztése
        </a><?php
    }
    ?><h2>Adatlap</h2>
    <h3>Per 100<?=$alapanyag['mertekegyseg']?></h3>
    <div class="alapanyag-adatlap">
        <div>Kalóriaérték</div>
        <div><?=$alapanyag['kaloria']?> kcal</div>
        <div>Szénhidrát</div>
        <div><?=$alapanyag['szenhidrat']?><?=$alapanyag['mertekegyseg']?></div>
        <div>Zsír</div>
        <div><?=$alapanyag['zsir']?><?=$alapanyag['mertekegyseg']?></div>
        <div>Fehérje</div>
        <div><?=$alapanyag['feherje']?><?=$alapanyag['mertekegyseg']?></div>
        <div>Cukor</div>
        <div><?=($alapanyag['cukor']) ? 'tartalmaz' : 'mentes'?></div>
        <div>Glutén</div>
        <div><?=($alapanyag['gluten']) ? 'tartalmaz' : 'mentes'?></div>
        <div>Laktóz</div>
        <div><?=($alapanyag['laktoz']) ? 'tartalmaz' : 'mentes'?></div>
    </div>
</div>