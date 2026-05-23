<?php
// Alap includolások
require('./includes/config.inc.php');
require('./includes/autoload.inc.php');
require('./includes/external/htmlpurifier/HTMLPurifier.standalone.php');

use Kaloriafalo\Classes\MySQLHandler;
use Kaloriafalo\Classes\Controller;
use Kaloriafalo\Classes\Helpers;
use Kaloriafalo\Classes\Logging;
use Kaloriafalo\Classes\Oldalgyujto;
use Kaloriafalo\Classes\Settings;

define("ROOT_DIR", __DIR__);
define("DEBUG_MODE", true);
define("ROOT_PATH", $GLOBALS['RootPath']);
$uid = null;
$dbcallcount = 0;

//print_r($_GET);

// Cookie biztonság beállítása
$isHttps =
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 604800,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Cross site reference támadások elleni védelem inicializálása
if(!isset($_SESSION['csrf_token']))
    Controller::CSRFGenerator();

// Bot detektálás
Logging::BotDetection($botscore, $bot_ok);

// Cache beállítása
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Cross Site Scripting elleni védelem
$nonce = base64_encode(random_bytes(16));
Settings::$nonce = $nonce;
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'none'; require-trusted-types-for 'script'");

// Session adatok bekérése az adatbázisból, és frissítése, ha létezik
$session_id = Logging::SessionDB();
Logging::SessionSeen($session_id);
Settings::$session_id = $session_id;

// Alapvető $_GET és $_SESSION műveletek lebonyolítása, és kilépés
if(isset($_GET['page']) && $_GET['page'] == "kilepes")
{
	session_unset();
	session_destroy();
	setcookie(session_name(), '', time() - 3600, '/');
	header("Location: " . ROOT_PATH);
	exit;
}

// Ha van aktív bejelentkezett session-je a felhasználónak, vizsgáljuk meg közelebbről
if(isset($_SESSION['uid']) && $_SESSION['uid'])
{
	// Ha változott a useragent, azonnali kihajítás
	if ($_SESSION['useragent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
		session_unset();
		session_destroy();
		// TODO valamit megírni ide
		exit;
	}

	// Következő lépésben ellenőrizzük, hogy továbbra is jogosult-e bejelentkezve lenni,
	// vagy nem változott-e a felhasználói szintje
	// allapot 2 = aktív, 1 = letiltott, 0 = törölt
	// szint 3 = tulaj, 2 = admin, 1 = regisztrált felhasználó
	$userverify = new MySQLHandler();
	$userverify->Prepare('SELECT felhasznalok.felhasznalo_id AS felhasznalo_id, usernev, szint, fajl
			FROM felhasznalok
				LEFT JOIN feltoltesek ON felhasznalok.profilkep = feltoltesek.feltoltes_id
			WHERE felhasznalok.felhasznalo_id = ? AND allapot = 2');
	$userverify->Run($_SESSION['uid']);

	if($userverify->sorokszama == 0)
	{
		session_unset();
		session_destroy();
		setcookie(session_name(), '', time() - 3600, '/');
		$_SESSION['uid'] = false;
	}
	else
	{
		$userverify->Bind($uid, $usernev, $rang, $profilkep);
		Settings::$usernev = $usernev;
		Settings::$profilkep = $profilkep;

		switch($rang) {
			case 3: Settings::$foadmin = true; // fallthrough
			case 2: Settings::$admin = true; // fallthrough
			case 1: Settings::$uid = $uid;
		}
	}
}
else
{
	$_SESSION['uid'] = false;
}

// Alap script fájlok hozzáadása
Settings::$jsfiles[] = "includes/external/sweetalert/sweetalert2.all.min.js";
Settings::$jsfiles[] = "Pages/views/_assets/js/sitefunctions.js";
Settings::$PHPvarsToJS['csrf_token'] = $_SESSION['csrf_token'];
Settings::$PHPvarsToJS['RootPath'] = ROOT_PATH;

// Üzleti logika, és view kiválasztás
$controller = new Controller();
$controller->Run();
$page = $controller->page;

// Oldal megjelenítése
require("./template/index.tpl.php");

$_SESSION['response_codes'][] = http_response_code();
$_SESSION['useragent'] = $_SERVER['HTTP_USER_AGENT'];
$responseTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
if(http_response_code() == 200)
	Logging::Latogatas($responseTime, $botscore, $bot_ok, $session_id, $dbcallcount);
else
	Logging::PageFault($session_id, $responseTime);
