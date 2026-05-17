<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=ucfirst($this->kategoriacimke['cimke_nev'])?></h1>
    <form action="<?=ROOT_PATH?>/cimke/szerkeszt/<?=$this->kategoriacimke['slug']?>" method="post">
        <?=$this->form->Render();?>
        <div class="submit"><input type="submit" value="Cimke szerkesztése"></div>
    </form>
</div>