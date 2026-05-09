<?php

use Kaloriafalo\Classes\Helpers;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1><?=ucfirst($this->hutoszekreny['huto']['huto_nev'])?></h1><?php
    if($this->irasjog) {
        ?><a href="<?= ROOT_PATH . '/hutoszekreny/szerkeszt/' . $this->hutoszekreny['huto']['huto_id'] ?>">
            Hűtő szerkesztése
        </a>
        <h2>Hűtő elemeinek szerkesztése</h2>
        <form action="<?=ROOT_PATH?>/hutoszekreny/tartalomszerkeszt/<?=$this->hutoszekreny['huto']['huto_id']?>" method="post">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <input type="hidden" name="huto_id" value="<?=$this->hutoszekreny['huto']['huto_id']?>">
            <div id="alapanyagok"><?php
                foreach($this->hutoszekreny['tartalom'] as $alapanyag) {
                    ?><div class="alapanyag-sor">
                        <input type="text" class="autocomplete" value="<?= $alapanyag['alapanyag'] ?>">
                        <input type="hidden" name="alapanyagok[]" value="<?= $alapanyag['alapanyag_id'] ?>">
                    </div><?php
                }
                ?><div class="alapanyag-sor">
                    <input type="text" class="autocomplete">
                    <input type="hidden" name="alapanyagok[]">
                </div>
            </div>
            <div class="submit"><input type="submit" value="Hűtő tartalmának mentése"></div>
        </form><?php
    }
?></div>