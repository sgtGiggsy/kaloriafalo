<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=$this->recept['recept_nev']?>> szerkesztése</h1>
    <form action="<?=ROOT_PATH?>/recept/szerkeszt/<?=$this->recept['slug']?>" method="post" enctype="multipart/form-data">
        <?=$this->form->Render();?>
        <div class="submit"><input type="submit" value="Recept mentése"></div>
    </form>
</div>