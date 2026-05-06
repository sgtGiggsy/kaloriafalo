<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;

class AlapanyagDB
{
    public static function GetAlapanyag(string $slug) : ?array {
        $alapanyag = new MySQLHandler("SELECT alapanyag_nev, alapanyag_id, kaloria, mertekegyseg, feherje, zsir, szenhidrat, cukor, gluten, laktoz, slug
            FROM alapanyagok WHERE slug = ?;", $slug);
        if($alapanyag->sorokszama == 0)
            return null;
        else
            return $alapanyag->AsArray()[0];
    }

    public static function GetAlapanyagok() : ?array {
        $alapanyag = new MySQLHandler("SELECT alapanyag_nev AS alapanyag, kaloria AS kalória, mertekegyseg AS mértékegység, szenhidrat AS szénhidrát, feherje AS fehérje, zsir AS zsír, IF(cukor, '*', '') AS cukor, IF(gluten, '*', '') AS glutén, IF(laktoz, '*', '') AS laktóz, slug
            FROM alapanyagok
            ORDER BY alapanyag_nev;");
        if($alapanyag->sorokszama == 0)
            return null;
        else
            return $alapanyag->AsArray();
    }

    public static function GetAlapanyagokFuzzyList(array $needlarray) : array {
        $alapanyag = new MySQLHandler();
        $alapanyag->Prepare('SELECT alapanyag_nev AS alapanyag, slug
                FROM alapanyagok
                WHERE alapanyag_nev LIKE ?;');
        return $alapanyag->GetFuzzyList($needlarray);
    }

    public static function CheckIrasjog(string $slug, int $uid) : bool {
        $irasjog = new MySQLHandler("SELECT slug FROM alapanyagok WHERE alapanyag_id = ? AND felhasznalo_id = ?;", $slug, $uid);
        if($irasjog->sorokszama == 0)
            return false;
        else
            return true;
    }

    public static function UjAlapanyag (string $alapanyag_nev, string $slug, int $kaloria, ?float $szenhidrat, ?float $feherje, ?float $zsir, string $mertekegyseg, int $felhasznalo_id, ?int $cukor, ?int $gluten, ?int $laktoz) : bool {
        $ujalapanyag = new MySQLHandler("INSERT INTO alapanyagok (alapanyag_nev, kaloria, szenhidrat, feherje, zsir, slug, felhasznalo_id, mertekegyseg, cukor, gluten, laktoz)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $alapanyag_nev, $kaloria, $szenhidrat, $feherje, $zsir, $slug, $felhasznalo_id, $mertekegyseg, $cukor, $gluten, $laktoz);
        return $ujalapanyag->siker;
    }

    public static function AlapanyagSzerkeszt (string $alapanyag_nev, int $kaloria, ?float $szenhidrat, ?float $feherje, ?float $zsir, string $mertekegyseg, ?int $cukor, ?int $gluten, ?int $laktoz, string $slug) : bool {
        $alapanyag = new MySQLHandler("UPDATE alapanyagok SET alapanyag_nev = ?, kaloria = ?, szenhidrat = ?, feherje = ?, zsir = ?, mertekegyseg = ?, cukor = ?, gluten = ?, laktoz = ?
            WHERE slug = ?",
            $alapanyag_nev, $kaloria, $szenhidrat, $feherje, $zsir, $mertekegyseg, $cukor, $gluten, $laktoz, $slug);
        return $alapanyag->siker;
    }
}