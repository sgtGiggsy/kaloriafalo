<?php
function HtmlHead(&$keywords, &$canonical, &$title, &$ogtype, &$publishtime, &$shareimage, &$robots, &$ablakcim, &$sitedesc, &$cimke) : void
{
    $keywords = array();
    $canonical = ROOT_PATH;
    $title .= " - Főoldal";
    $ogtype = "website";
    $publishtime = null;
    $ablakcim = $title;
    $robots = null;
    $sitedesc = "A keresett tartalom nem létezik vagy eltávolították.";
    $cimke = "főoldal";
    $shareimage = null;
}

function Render() : void
{
    ?><h1>Főoldal</h1>
    <p>Itt valami nagyszerű dolog készül!</p><?php
}