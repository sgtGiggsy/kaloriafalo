<?php

use Kaloriafalo\Classes\Settings;
use Kaloriafalo\template\GrafikaiElemek;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <div class="kereses">
        <input type="text" name="kereses" id="kereses" placeholder="<?=$this->keresesplaceholder?>">
        <input type="submit" id="keresobutton" value="keresés">
    </div>
    <h1>Receptek</h1>
    <div class="receptlista"><?php
        foreach ($this->recept as $recept) {
            if($recept['mentve']) {
                $ures = '';
                $teli = 'active';
            }
            else {
                $ures = 'active';
                $teli = '';
            }
            ?><a href="<?=ROOT_PATH?>/recept/<?=$recept['slug']?>">
                <div class="receptelem">
                    <div class="receptelemkep"><img src="<?=ROOT_PATH . $recept['kepurl']?>" alt="Kép <?=$recept['recept_nev']?>-ről"/></div>
                    <div class="receptadatok">
                        <h2><?=$recept['recept_nev']?></h2>
                        <div class="spreader"></div>
                        <div class="userinterakciok">
                            <div class="bookmarking" id="bookmark-<?= $recept['recept_id'] ?>">
                                <?=sprintf(GrafikaiElemek::$ikonok['bookmark_filled'], $teli, $recept['recept_id'])?>
                                <?=sprintf(GrafikaiElemek::$ikonok['bookmark'], $ures, $recept['recept_id'])?>
                            </div>
                            <div class="spreader"></div>
                            <div class="ertekeles" title="Átlag: <?=($recept['ertekeles'] ?? 0) . ' a ' . $recept['ertekelesek_szama']?> értékelésből">
                                <?=GrafikaiElemek::Ertekeles($recept['ertekeles'], $recept['recept_id'])?>
                            </div>
                        </div>
                    </div>
                </div>
            </a><?php
        }
    ?></div>
    <?=$this->Lapozo()?>
</div>