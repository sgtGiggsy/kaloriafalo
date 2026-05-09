<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;
use Kaloriafalo\Classes\Settings;

class ReceptDB
{
    public static function UjRecept(string $recept_nev, string $recept_szoveg, int $lathatosag, $slug, ?string $adagmeret, array $alapanyagok, $uid) : bool {
        $sikeresdb = true;
        $allergenek = $alapanyagok['allergenek'];
        $receptgyarto = new MySQLHandler();
        $receptgyarto->StartTransaction();
        $receptgyarto->Prepare('INSERT INTO receptek (recept_nev, recept_szoveg, slug, felhasznalo_id, lathatosag, adagmeret, cukor, laktoz, gluten, modosito_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
        $receptgyarto->Run($recept_nev, $recept_szoveg, $slug, $uid, $lathatosag, $adagmeret, $allergenek['cukor'], $allergenek['laktoz'], $allergenek['gluten'], $uid);
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
        $recept = new MySQLHandler("SELECT * FROM receptek WHERE slug = ?;", $slug);
        if($recept->sorokszama == 0)
            return null;
        else
            return $recept->AsArray()[0];
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