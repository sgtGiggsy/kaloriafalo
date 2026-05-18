<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?>
<h1>Új recept felvitele</h1>
<form action="<?=ROOT_PATH?>/recept/uj" method="post" enctype="multipart/form-data" class="receptoldal">
    <div class="formbuilder">
        <div class="inputcont-text">
            <label for="recept_nev">Recept neve</label>
            <input type="text" name="recept_nev" id="recept_nev" placeholder="Recept neve" required/>
        </div>
        <div id="alapanyagok" class="inputcont-text">
            <label for="alapanyagok">Alapanyagok</label>
            <div class="alapanyag-sor">
                <input type="text" class="autocomplete">
                <input type="hidden" name="alapanyagok[0][alapanyag]">
                <input type="hidden" name="alapanyagok[0][mennyiseg]">
            </div>
        </div>
        <?=$this->form->Render();?>

        <div class="submit"><input type="submit" value="Recept felvitele"></div>
    </div>
    <div>
        <?=$this->Cimkeform(null, true)?>
        <div>
            <label for="kepek">Kép csatolása a recepthez</label>
            <div class="dropzone" id="dropzone">
                <p>Húzd ide a fájlokat vagy kattints</p>
                <input type="file" name="kepek[]" id="kepek" accept="image/jpeg, image/png, image/bmp, image/webp" multiple>
            </div>
            <div id="preview"></div>
        </div>
    </div>
</form>