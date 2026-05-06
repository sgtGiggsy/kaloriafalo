<?php

namespace Kaloriafalo\Classes;

use Kaloriafalo\Pages\Alapanyag;
use Kaloriafalo\Pages\Page;
use Kaloriafalo\Pages\Api;
use Kaloriafalo\Pages\Autentikacio;
use Kaloriafalo\Pages\Recept;
use Kaloriafalo\Pages\Beallitasok;
use Kaloriafalo\Pages\Felhasznalo;
use Kaloriafalo\Pages\Hutoszekreny;
use Kaloriafalo\Pages\SinglePage;

class Oldalgyujto
{
    public static array $oldalak = [
        'alapanyag' => [
            'menupont' => 'Alapanyag',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => Alapanyag::class],
        'alapanyagok' => [
            'menupont' => 'Alapanyagok',
            'lathatosag' => 'mindenki',
            'menuben' => true,
            'sorrend' => 40,
            'szulo' => null,
            'ikon' => null,
            'handler' => Alapanyag::class],
        'belepes' => [
            'menupont' => 'Belépés',
            'lathatosag' => 'vendeg',
            'menuben' => true,
            'sorrend' => 80,
            'szulo' => null,
            'ikon' => null,
            'handler' => Autentikacio::class],
        'regisztracio' => [
            'menupont' => 'Regisztráció',
            'lathatosag' => 'vendeg',
            'menuben' => true,
            'sorrend' => 81,
            'szulo' => null,
            'ikon' => null,
            'handler' => Autentikacio::class],
        'elfelejtettjelszo' => [
            'menupont' => 'Elfelejtett jelszó',
            'lathatosag' => 'vendeg',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => Autentikacio::class],
        'jelszocsere' => [
            'menupont' => 'Jelszócsere',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 75,
            'szulo' => 'profil',
            'ikon' => null,
            'handler' => Autentikacio::class],
        'adminisztracio' => [
            'menupont' => 'Adminisztráció',
            'lathatosag' => 'adminok',
            'menuben' => true,
            'sorrend' => 60,
            'szulo' => true,
            'ikon' => 'fogaskerek',
            'handler' => Beallitasok::class],
        'beallitasok' => [
            'menupont' => 'Beállítások',
            'lathatosag' => 'adminok',
            'menuben' => true,
            'sorrend' => 61,
            'szulo' => 'adminisztracio',
            'ikon' => null,
            'handler' => Beallitasok::class],
        'felhasznalo' => [
            'menupont' => 'Felhasználó',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 70,
            'szulo' => true,
            'ikon' => 'sefsapka',
            'handler' => Felhasznalo::class],
        'profil' => [
            'menupont' => 'Profil',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 72,
            'szulo' => 'felhasznalo',
            'ikon' => null,
            'handler' => Felhasznalo::class],
        'profilszerkesztes' => [
            'menupont' => 'Beállítások',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 74,
            'szulo' => 'felhasznalo',
            'ikon' => null,
            'handler' => Felhasznalo::class],
        'kilepes' => [
            'menupont' => 'Kilépés',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 76,
            'szulo' => 'felhasznalo',
            'ikon' => null,
            'handler' => null],
        'felhasznalok' => [
            'menupont' => 'Felhasználók',
            'lathatosag' => 'adminok',
            'menuben' => true,
            'sorrend' => 63,
            'szulo' => 'adminisztracio',
            'ikon' => null,
            'handler' => Felhasznalo::class],
        'hutoszekreny' => [
            'menupont' => 'Hűtőszekrényem',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 20,
            'szulo' => null,
            'ikon' => null,
            'handler' => Hutoszekreny::class],
        'hutoszekrenyek' => [
            'menupont' => 'Hűtőszekrények',
            'lathatosag' => 'adminok',
            'menuben' => true,
            'sorrend' => 65,
            'szulo' => 'adminisztracio',
            'ikon' => null,
            'handler' => Hutoszekreny::class],
        '403' => [
            'menupont' => '',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class],
        '404' => [
            'menupont' => '',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class],
        'fooldal' => [
            'menupont' => 'Főoldal',
            'lathatosag' => 'mindenki',
            'menuben' => true,
            'sorrend' => 0,
            'szulo' => null,
            'ikon' => 'oldalminilogo',
            'handler' => SinglePage::class],
        'adatkezelesi-tajekoztato' => [
            'menupont' => 'Adatkezelési tájékoztató',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class],
        'security' => [
            'menupont' => 'Biztonsági irányelvek',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class],
        'recept' => [
            'menupont' => 'Recept',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => Recept::class],
        'receptek' => [
            'menupont' => 'Receptek',
            'lathatosag' => 'mindenki',
            'menuben' => true,
            'sorrend' => 10,
            'szulo' => null,
            'ikon' => null,
            'handler' => Recept::class],
        'szakacskonyv' => [
            'menupont' => 'Szakácskönyvem',
            'lathatosag' => 'tagok',
            'menuben' => true,
            'sorrend' => 30,
            'szulo' => null,
            'ikon' => null,
            'handler' => Recept::class],
        'impresszum' => [
            'menupont' => '',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class],
        'media-ajanlat' => [
            'menupont' => '',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class],
        'kapcsolat' => [
            'menupont' => '',
            'lathatosag' => 'mindenki',
            'menuben' => false,
            'sorrend' => null,
            'szulo' => null,
            'ikon' => null,
            'handler' => SinglePage::class]
    ];

    public static function Menupontok() : array {
        $rendezett = array_filter(self::$oldalak, function ($item) {
            return $item['menuben'] == true;
        });

        uasort($rendezett, function ($a, $b) {
            return $a['sorrend'] <=> $b['sorrend'];
        });

        return $rendezett;
    }
}