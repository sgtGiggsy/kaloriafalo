<?php

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
$recept = $this->recept['recept'];
$alapanyagok = $this->recept['alapanyagok'];
?><div class="normalcontent">
    <h1><?=ucfirst($recept['recept_nev'])?></h1><?php
    if($this->irasjog) {
        ?><a href="<?= ROOT_PATH . '/recept/szerkeszt/' . $recept['slug'] ?>">
            Recept szerkesztése
        </a><?php
    }
    ?><h2>Adatlap</h2>
    <h3>Összetevők</h3>
    <div class="alapanyagok">
        <ul><?php
            foreach($alapanyagok as $alapanyag) {
                ?><li><?=$alapanyag['mennyiseg']?> <?=$alapanyag['mertekegyseg']?> <?=$alapanyag['alapanyag_nev']?></li><?php
            }
        ?></ul>
    </div>
    <h3>Elkészítés módja</h3>
    <div class="elkeszitesmod">
        <?=$recept['recept_szoveg']?>
    </div>
</div>