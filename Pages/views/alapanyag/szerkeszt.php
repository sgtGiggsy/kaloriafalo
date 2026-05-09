<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=ucfirst($this->alapanyag['alapanyag_nev'])?></h1>
    <form action="<?=ROOT_PATH?>/alapanyag/szerkeszt/<?=$this->alapanyag['slug']?>" method="post">
        <?=$this->form->Render();?>
        <div class="submit"><input type="submit" value="Alapanyag szerkesztése"></div>
    </form>
</div>