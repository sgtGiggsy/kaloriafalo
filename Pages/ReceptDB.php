<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;
use Kaloriafalo\Classes\Settings;

class ReceptDB
{
    public static function UjRecept(string $recept_nev, string $recept_szoveg, int $lathatosag, $slug, ?string $adagmeret, array $alapanyagok, ?int $elokeszuletek, ?int $sutesido, int $uid) : bool {
        $sikeresdb = true;
        $allergenek = $alapanyagok['allergenek'];
        $receptgyarto = new MySQLHandler();
        $receptgyarto->StartTransaction();
        $receptgyarto->Prepare('INSERT INTO receptek (recept_nev, recept_szoveg, slug, felhasznalo_id, lathatosag, adagmeret, cukor, laktoz, gluten, elokeszuletek, sutesido, modosito_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
        $receptgyarto->Run($recept_nev, $recept_szoveg, $slug, $uid, $lathatosag, $adagmeret, $allergenek['cukor'], $allergenek['laktoz'], $allergenek['gluten'], $elokeszuletek, $sutesido, $uid);
        if(!$receptgyarto->siker) {
            $sikeresdb = false;
        }
        else {
            print_r($alapanyagok);
            $recept_id = $receptgyarto->last_insert_id;
            $alapanyagok = $alapanyagok['alapanyagok'];
            foreach ($alapanyagok as $alapanyag) {
                if(!$alapanyag['alapanyag_id'])
                    break;

                $receptgyarto->Prepare('INSERT INTO recept_alapanyagok (recept_id, alapanyag_id, mertekegyseg, mennyiseg) VALUES (?, ?, ?, ?);');
                $receptgyarto->Run($recept_id, $alapanyag['alapanyag_id'], $alapanyag['mertekegyseg'], $alapanyag['mennyiseg']);
                if(!$receptgyarto->siker) {
                    $sikeresdb = false;
                    break;
                }
            }
        }
        if(!$sikeresdb)
            $receptgyarto->Rollback();
        else
            $receptgyarto->Commit();

        return $sikeresdb;
    }

    public static function GetRecept(string $slug) : ?array {
        $recept = new MySQLHandler("SELECT recept_nev, recept_szoveg, letrehozas_ideje, slug, elokeszuletek, sutesido, lathatosag,
                    receptek.cukor AS cukor, receptek.gluten AS gluten, receptek.laktoz AS laktoz, recept_id, felhasznalo_id
                FROM receptek WHERE slug = ?;", $slug);
        if($recept->sorokszama == 0)
            return null;

        $recept = $recept->EscapedArray('recept_szoveg')[0];
        $alapanyagok = new MySQLHandler("SELECT alapanyag_nev, recept_alapanyagok.mertekegyseg AS mertekegyseg, mennyiseg
                FROM recept_alapanyagok
                    INNER JOIN alapanyagok ON alapanyagok.alapanyag_id = recept_alapanyagok.alapanyag_id
                WHERE recept_alapanyagok.recept_id = ?;", $recept['recept_id']);

        $kepek = new MySQLHandler("SELECT fajl FROM recept_kepek
                    INNER JOIN feltoltesek ON feltoltesek.feltoltes_id = recept_kepek.feltoltes_id  
                WHERE recept_id = ?;", $recept['recept_id']);

        return ['recept' => $recept, 'alapanyagok' => $alapanyagok->EscapedArray(), 'kepek' => $kepek->EscapedArray()];
    }

    public static function GetReceptek() : array {
        $receptek = new MySQLHandler("SELECT recept_nev, lathatosag, receptek.recept_id AS recept_id,
                    slug, cukor, gluten, laktoz, fajl AS kepurl,
                    AVG(recept_ertekelesek.ertekeles) AS ertekeles,
                    COUNT(recept_ertekelesek.ertekeles) AS ertekelesek_szama,
                    IF(szakacskonyvrecept_id, 1, 0) AS mentve
                FROM receptek
                    LEFT JOIN recept_ertekelesek ON recept_ertekelesek.recept_id = receptek.recept_id
                    LEFT JOIN recept_kepek ON recept_kepek.recept_id = receptek.recept_id
                    LEFT JOIN feltoltesek ON feltoltesek.feltoltes_id = recept_kepek.feltoltes_id
                    LEFT JOIN szakacskonyv_receptek ON szakacskonyv_receptek.recept_id = receptek.recept_id
                    LEFT JOIN szakacskonyvek ON szakacskonyv_receptek.szakacskonyv_id = szakacskonyvek.szakacskonyv_id
                WHERE (lathatosag = 1 OR lathatosag = 0 AND receptek.felhasznalo_id = ?)
                    AND (recept_kepek.elsodleges IS NULL OR recept_kepek.elsodleges = 1)
                    AND (szakacskonyvrecept_id IS NULL OR szakacskonyvek.felhasznalo_id = ?)
                GROUP BY receptek.recept_id;", Settings::$uid, Settings::$uid);
        return $receptek->EscapedArray();
    }

    public static function GetReceptIrasjog(int $felhasznalo_id, int $elem_id) : bool {
        $irasjog = new MySQLHandler("SELECT slug FROM receptek WHERE recept_id = ? OR slug = ? AND felhasznalo_id = ?;", $elem_id, $elem_id, $felhasznalo_id);
        if($irasjog->sorokszama == 0)
            return false;
        else
            return true;
    }

    public static function Ertekeles(int $uid, int $recept_id, int $ertekelesertek) : bool {
        $ertekeles = new MySQLHandler();
        $ertekeles->StartTransaction();
        $ertekeles->Prepare("INSERT INTO recept_ertekelesek (ertekeles, felhasznalo_id, recept_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE ertekeles = VALUES(ertekeles);");
        $ertekeles->Run($ertekelesertek, $uid, $recept_id);

        return $ertekeles->siker;
    }
}