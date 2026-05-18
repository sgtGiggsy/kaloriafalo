<?php
if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
$recept = $this->recept['recept'];
$alapanyagok = $this->recept['alapanyagok'];
$kepek = $this->recept['kepek'];
$cimkek = $this->recept['cimkek'];
?><h1><?=$recept['recept_nev']?> szerkesztése</h1>
<form action="<?=ROOT_PATH?>/recept/szerkeszt/<?=$recept['slug']?>" method="post" enctype="multipart/form-data" class="receptoldal">
    <div class="formbuilder">
        <div class="inputcont-text">
            <label for="recept_nev">Recept neve</label>
            <input type="text" name="recept_nev" id="recept_nev" placeholder="Recept neve" value="<?=$recept['recept_nev']?>" required/>
        </div>
        <div id="alapanyagok" class="inputcont-text">
            <label for="alapanyagok">Alapanyagok</label><?php
            $iter = 0;
            foreach($alapanyagok as $alapanyag) {
                ?><div class="alapanyag-sor">
                    <input type="text" class="autocomplete" value="<?=$alapanyag['mennyiseg'] . " " . $alapanyag['mertekegyseg'] . " " . $alapanyag['alapanyag_nev'] ?>">
                    <input type="hidden" name="alapanyagok[<?=$iter?>][alapanyag]" value="<?= $alapanyag['alapanyag_id'] ?>">
                    <input type="hidden" name="alapanyagok[<?=$iter?>][mennyiseg]" value="<?= $alapanyag['mennyiseg'] . " " . $alapanyag['mertekegyseg'] ?>">
                </div><?php
                $iter++;
            }
            ?><div class="alapanyag-sor">
                <input type="text" class="autocomplete">
                <input type="hidden" name="alapanyagok[<?=$iter?>][alapanyag]">
                <input type="hidden" name="alapanyagok[<?=$iter?>][mennyiseg]">
            </div>
        </div>
        <?=$this->form->Render();?>

        <div class="submit"><input type="submit" value="Módosítások mentése"></div>
    </div>
    <div>
        <?=$this->Cimkeform($cimkek, true)?>
        <div>
            <label for="kepek">Kép csatolása a recepthez</label>
            <div class="dropzone" id="dropzone">
                <p>Húzd ide a fájlokat vagy kattints</p>
                <input type="file" name="kepek[]" id="kepek" accept="image/jpeg, image/png, image/bmp, image/webp" multiple>
            </div>
            <div id="preview"></div>
        </div>
        <div>
            <h3>Feltöltött képek</h3>
            <div class="receptgallery"><?php
                $iter = 0;
                foreach ($this->recept['kepek'] as $kep) {
                    ?><div>
                        <label for="meglevokepek[<?=$iter?>]">
                            <img src="<?=ROOT_PATH . $kep['fajl']?>" alt="Receptfotó">
                            <small>Megtart: </small><input type="checkbox" name="meglevokepek[]" id="meglevokepek[<?=$iter?>]" value="<?=$kep['receptkep_id']?>" checked>
                        </label>
                        <label><small>Borítókép: </small><input type="radio" name="boritokep" id="boritokep" value="<?=$kep['receptkep_id']?>" <?=($kep['elsodleges']) ? 'checked' : '' ?>></label>
                    </div><?php
                    $iter++;
                }
            ?></div>
        </div>
    </div>
</form>