<?php

use Kaloriafalo\Classes\Settings;
use Kaloriafalo\template\GrafikaiElemek;
use Kaloriafalo\Classes\Helpers;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="receptoldal">
    <div class="kereses">
        <input type="text" name="kereses" id="kereses" placeholder="<?=$this->keresesplaceholder?>">
        <input type="submit" id="keresobutton" value="keresés">
    </div>
    <div>
        <h1>Receptek</h1>
        <div class="receptlista"><?php
            if(count($this->recept) == 0) {
                if($this->params['method'] == 'kereses') {
                    echo '<h2>Nincs találat ' . Helpers::NeveloHatarozo($this->params['elemid']) . ' keresőkifejezése!</h2>';
                } else {
                    echo '<h2>Még nincsenek nyilvánosan elérhető receptek!</h2>';
                }
            } else {
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
                                <div class="userinterakciok"><?php
                                    if(Settings::$uid) {
                                        ?><div class="bookmarking" id="bookmark-<?= $recept['recept_id'] ?>">
                                            <?=sprintf(GrafikaiElemek::$ikonok['bookmark_filled'], $teli, $recept['recept_id'])?>
                                            <?=sprintf(GrafikaiElemek::$ikonok['bookmark'], $ures, $recept['recept_id'])?>
                                        </div><?php
                                    }
                                    ?><div class="spreader"></div>
                                    <div class="ertekeles" title="Átlag: <?=(round($recept['ertekeles'] ?? 0, 2)) . ' a ' . $recept['ertekelesek_szama']?> értékelésből">
                                        <?=GrafikaiElemek::Ertekeles($recept['ertekeles'], $recept['recept_id'])?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a><?php
                }
            }
        ?></div>
        <?=$this->Lapozo()?>
    </div>
    <div>
        <form action="<?=ROOT_PATH?>/receptek/kategoriak" method="post">
            <?=$this->Cimkeform()?>
            <?=$this->form->Render();?>
            <input type="submit" id="kategoria_szur" value="Szűrés"/>
            <input type="submit" id="szurtorol" value="Szűrők törlése"/>
        </form>
    </div>
</div>