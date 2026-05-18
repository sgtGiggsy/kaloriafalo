<?php

use Kaloriafalo\template\GrafikaiElemek;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
$recept = $this->recept['recept'];
$kepek = $this->recept['kepek'];
$fejkep = reset($kepek);
$tapanyagtabla = json_decode($recept['tapanyagtablazat'], true);
?><div class="header" style="background-image: url('<?=ROOT_PATH . $fejkep['fajl'] ?>')">
    <h1 style="display:none"><svg>
        <text x="0" y="50" stroke="black" fill="white">
            <?=ucfirst($recept['recept_nev'])?>
        </text>
    </svg></h1>
    <div class="receptadatlap">
        <h2>Recept adatlap</h2>
        <div>Adagméret</div>
        <div><?=$recept['adagmeret']?></div>
        <div>Előkészületek</div>
        <div><?=$recept['elokeszuletek']?> perc</div>
        <div>Sütési/főzési idő</div>
        <div><?=$recept['sutesido']?> perc</div>
        <div>Energia / adag</div>
        <div><?=round($tapanyagtabla['kaloria'] / $recept['adagmeret'] ?? 1, 2)?> kcal</div>
        <div>Szénhidrát / adag</div>
        <div><?=round($tapanyagtabla['szenhidrat'] / $recept['adagmeret'] ?? 1, 2)?> g</div>
        <div>Zsír / adag</div>
        <div><?=round($tapanyagtabla['zsir'] / $recept['adagmeret'] ?? 1, 2)?> g</div>
        <div>Fehérje / adag</div>
        <div><?=round($tapanyagtabla['feherje'] / $recept['adagmeret'] ?? 1, 2)?> g</div>
        <div>Értékelés</div>
        <div>
        <div class="ertekeles" title="Átlag: <?=(round($recept['ertekeles'] ?? 0, 2)) . ' a ' . $recept['ertekelesek_szama']?> értékelésből">
            <?=GrafikaiElemek::Ertekeles($recept['ertekeles'], $recept['recept_id'])?>
        </div>
        </div>
    </div>
</div>