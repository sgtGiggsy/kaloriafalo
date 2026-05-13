<?php

use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
?><div class="normalcontent">
    <h1>Felhasználók hűtőszekrényei</h1><?php
    Helpers::RenderArrayAsTable($this->hutoszekreny, 'hutoszekreny', 'huto_id', 'regulartable');
?></div>