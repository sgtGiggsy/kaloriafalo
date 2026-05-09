<?php
function HtmlHead(&$keywords, &$canonical, &$title, &$ogtype, &$publishtime, &$shareimage, &$robots, &$ablakcim, &$sitedesc, &$cimke) : void
{
    http_response_code(404);
    $canonical = ROOT_PATH;
    $title .= " - Oldal nem található";
    $ablakcim = $title;
    $robots = '<meta name="robots" content="noindex, nofollow">';
    $sitedesc = "A keresett tartalom nem létezik vagy eltávolították.";
    $cimke = "404";
}

function Render()
{
    ?><h1>404 - Nem található</h1>
    <p>Itt most semmi finomság nem készül!</p><?php
}