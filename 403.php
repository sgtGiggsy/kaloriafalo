<?php
function HtmlHead(&$keywords, &$canonical, &$title, &$ogtype, &$publishtime, &$shareimage, &$robots, &$ablakcim, &$sitedesc, &$cimke)
{
    http_response_code(403);
    $keywords = array();
    $canonical = ROOT_PATH;
    $title .= " - Hozzáférés megtagadva";
    $ogtype = "website";
    $publishtime = null;
    $ablakcim = $title;
    $robots = '<meta name="robots" content="noindex, nofollow">';
    $sitedesc = "Ehhez az oldalhoz nincs jogosultságod.";
    $cimke = "403";
    $shareimage = null;
}

function Render()
{
    ?><h1>403 - Hozzáférés megtagadva</h1>
    <p>Itt neked nem főztünk ki semmit!</p><?php
}