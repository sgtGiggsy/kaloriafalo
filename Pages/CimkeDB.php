<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;

class CimkeDB {
    public static function GetCimke(string $slug) : ?array {
        $cimke = new MySQLHandler("SELECT receptcimke_id, cimke_nev, slug FROM recept_cimkek WHERE slug = ?;", $slug);
        if($cimke->sorokszama == 0)
            return null;
        return $cimke->EscapedSingleElem();
    }

    public static function GetCimkek() : ?array {
        $cimkek = new MySQLHandler("SELECT receptcimke_id, cimke_nev, slug FROM recept_cimkek;");
        return $cimkek->EscapedArray();
    }
    public static function UjCimke(string $cimke_nev, string $slug) : bool {
        $ujcimke = new MySQLHandler("INSERT INTO recept_cimkek (cimke_nev, slug) VALUES (?, ?);", $cimke_nev, $slug);
        return $ujcimke->siker;
    }

    public static function CimkeSzerkeszt(string $cimke_nev, string $slug, ?string $ujslug) : bool {
        if($ujslug != null)
            $cimkeszerkeszt = new MySQLHandler("UPDATE recept_cimkek SET cimke_nev = ?, slug = ? WHERE slug = ?;", $cimke_nev, $ujslug, $slug);
        else
            $cimkeszerkeszt = new MySQLHandler("UPDATE recept_cimkek SET cimke_nev = ? WHERE slug = ?;", $cimke_nev, $slug);
        return $cimkeszerkeszt->siker;
    }

}