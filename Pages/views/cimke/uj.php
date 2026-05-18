<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Új címke felvitele</h1>
    <form action="<?=ROOT_PATH?>/cimke/uj" method="post">
        <?=$this->form->Render();?>
        <div class="submit"><input type="submit" value="Cimke létrehozása"></div>
    </form>
</div>