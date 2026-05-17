<?php
function HtmlHead(&$keywords, &$canonical, &$title, &$ogtype, &$publishtime, &$shareimage, &$robots, &$ablakcim, &$sitedesc, &$cimke)
{
    http_response_code(401);
    $canonical = ROOT_PATH;
    $title .= " - Bejelentkezés szükséges!";
    $publishtime = null;
    $ablakcim = $title;
    $robots = '<meta name="robots" content="noindex, nofollow">';
    $sitedesc = "Ennek az oldalnak a megtekintéséhez be kell jelentkezned!";
    $cimke = "401";
}

function Render()
{
    ?><h1>Bejelentkezéshez kötött tartalom</h1>
    <p>Itt csak belépést követően főzöcskézhetsz!</p><?php
}