<?php
namespace Kaloriafalo\template;

?><!DOCTYPE html>
<html lang="hu">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?=ROOT_PATH?>/template/style.css?v=2.37" type="text/css">
	<link rel="stylesheet" href="<?=ROOT_PATH?>/includes/external/sweetalert/sweetalert2.min.css" type="text/css">
    <link rel="shortcut icon" href="<?=ROOT_PATH?>/favicon.ico" type="image/x-icon">
	<!-- Facebook Open Graph rész -->
    <?php
    /** @var Controller $page */
	$page->HtmlHead(); ?>
</head>

<body>
    <!-- Menü -->
    <nav class="mainmenu" id="mainmenu"><?php
        include("./template/menu.tpl.php");
	?></nav>

    <!-- Fejléc -->
    <header><?php
        $page->RenderHeader();
    ?></header>

    <!--Tartalom-->
    <main><?php
        $page->Render();
    ?></main>

    <!-- Lábléc -->
    <footer><?php
        include("./template/footer.tpl.php");
    ?></footer>
    <div id="overlay" onclick="showMenu(false)"></div><?php

    include('./Pages/views/_assets/scriptblock.php')
?></body>
</html>