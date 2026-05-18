<?php

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
$recept = $this->recept['recept'];
$alapanyagok = $this->recept['alapanyagok'];
?><div class="receptoldal">
    <h1><?=ucfirst($recept['recept_nev'])?></h1><?php
    if($this->irasjog) {
        ?><a class="anchbutton" href="<?= ROOT_PATH . '/recept/szerkeszt/' . $recept['slug'] ?>">
            Recept szerkesztése
        </a><?php
    }
    else {
        echo "<div></div>";
    }
    ?><div class="elkeszitesmod">
        <h2>Elkészítés módja</h2>
        <?=$recept['recept_szoveg']?>
    </div>

    <div>
        <h2>Összetevők</h2>
        <div class="alapanyagok">
            <ul><?php
                foreach($alapanyagok as $alapanyag) {
                    ?><li><?=$alapanyag['mennyiseg']?> <?=$alapanyag['mertekegyseg']?> <?=$alapanyag['alapanyag_nev']?></li><?php
                }
            ?></ul>
        </div>

        <h2>Galéria</h2>
        <div class="receptgallery"><?php
            foreach ($this->recept['kepek'] as $kep) {
                echo '<img src="' . ROOT_PATH . $kep['fajl'] . '" alt="Receptfotó">';
            }
        ?></div>
    </div>
</div>
<div class="receptgalleryoverlay" id="receptgalleryoverlay">
    <span class="receptimgclose" id="closeBtn">✕</span>
    <img id="overlayImg" alt="Nagyított kép">
</div>