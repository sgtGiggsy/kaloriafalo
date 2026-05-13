<?php

use Kaloriafalo\Classes\Helpers;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="doublecolumn">
    <div>
        <h1><?=($this->hutoszekreny['huto']['huto_nev']) ?
                    ucfirst($this->hutoszekreny['huto']['huto_nev'])
                    : $this->hutoszekreny['huto']['usernev'] . ' hűtőszekrénye' ?></h1><?php
        if($this->irasjog) {
            ?><a class="anchbutton" href="<?= ROOT_PATH . '/hutoszekreny/szerkeszt/' . $this->hutoszekreny['huto']['huto_id'] ?>">
                Hűtő szerkesztése
            </a>
        </div>
        <div>
            <h2>Hűtőm elemeinek szerkesztése</h2>
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
            </form>
        </div>
        <div class="hutolehetosegek">
            <h2>Lehetőségeim</h2><?php
                $elso = true;
                $elozohianyzo = 0;
                foreach($this->elerhetoreceptek as $elerheto) {
                    if($elso) {
                        $elozohianyzo = $elerheto['hianyzo_db'];
                        $elso = false;
                        if($elerheto['hianyzo_db'] != 0) {
                            echo "<h3>Nincs olyan recept, amihez minden elérhető!</h3>";
                            echo "<h3>Receptek $elozohianyzo darab hiányzó összetevővel:</h3>";
                        }
                    }
                    if($elozohianyzo != $elerheto['hianyzo_db']) {
                        $elozohianyzo = $elerheto['hianyzo_db'];
                        echo "<h3>Receptek $elozohianyzo darab hiányzó összetevővel:</h3>";
                    }
                    ?><strong><?= $elerheto['recept_nev'] ?></strong><?php
                    foreach($elerheto['hianyzo'] as $hianyzo) {
                        ?><small><?= $hianyzo['alapanyag_nev'] ?></small><?php
                    }

            }
        ?></div><?php
    }
?></div>