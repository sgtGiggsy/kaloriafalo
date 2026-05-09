<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=ucfirst($this->hutoszekreny['huto_nev'])?></h1>
    <form action="<?=ROOT_PATH?>/hutoszekreny/szerkeszt/<?=$this->hutoszekreny['huto_id']?>" method="post">
        <?=$this->form->Render();?>
        <div class="submit"><input type="submit" value="Hűtő szerkesztése"></div>
    </form>
</div>