<?php

use Kaloriafalo\Classes\FormBuilder;

if (!defined('ROOT_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
$recept = $this->recept['recept'];
$kepek = $this->recept['kepek'];
?><div style="height: 200px; background-image: url('<?=ROOT_PATH . $kepek[0]['fajl'] ?>')"></div>