<?php

use Kaloriafalo\Classes\Settings;
use Kaloriafalo\template\GrafikaiElemek;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><h1>Cimkék</h1>
<div class="cimkefelho"><?php
    foreach ($cimkek as $cimke) {
        $checked = !empty($cimke['kivalasztva']) ? 'checked' : '';
        ?><label class="cimke-item">
            <input
                type="checkbox"
                name="cimke[]"
                value="<?=$cimke['receptcimke_id']?>"
                <?= $checked ?>
            >
            <span><?=$cimke['cimke_nev']?></span>
        </label><?php
    }
?></div>