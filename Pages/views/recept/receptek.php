<?php

use Kaloriafalo\Classes\Settings;
use Kaloriafalo\template\GrafikaiElemek;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Receptek</h1><?php
    if(Settings::$uid) {
        ?><a href="<?= ROOT_PATH . '/recept/uj' ?>">
            Új recept felvitele
        </a>
        <div class="receptlista"><?php
        foreach ($this->recept as $recept) {
            ?><a href="<?=ROOT_PATH?>/recept/<?=$recept['slug']?>">
                <div class="receptelem">
                    <div class="receptelemkep"><img src="<?=ROOT_PATH . '/' . $recept['kepurl']?>" alt="Kép <?=$recept['recept_nev']?>-ről"/></div>
                    <div class="receptadatok">
                        <h2><?=$recept['recept_nev']?></h2>
                        <div class="spreader"></div>
                        <div class="userinterakciok">
                            <div class="bookmark">
                                <?=($recept['mentve']) ? GrafikaiElemek::$ikonok['bookmark_filled'] : sprintf(GrafikaiElemek::$ikonok['bookmark'], $recept['recept_id'])?>
                            </div>
                            <div class="spreader"></div>
                            <div class="ertekeles" title="Átlag: <?=($recept['ertekeles'] ?? 0) . ' a ' . $recept['ertekelesek_szama']?> értékelésből">
                                <?=GrafikaiElemek::Ertekeles($recept['ertekeles'])?>
                            </div>
                        </div>
                    </div>
                </div>
            </a><?php
        }
        ?></div><?php
    }
?></div>