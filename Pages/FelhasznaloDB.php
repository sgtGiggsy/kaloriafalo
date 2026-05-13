<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;

class FelhasznaloDB
{
    public static function GetFelhasznalo(?int $felhasznalo_id) : ?array
    {
        if(!$felhasznalo_id)
            return null;

        $felhasznalo = new MySQLHandler("SELECT usernev, email, teljesnev, fajl AS profilkep, felhasznalok.felhasznalo_id AS felhasznalo_id, szint, regisztracio
            FROM felhasznalok
                LEFT JOIN feltoltesek ON felhasznalok.profilkep = feltoltesek.feltoltes_id
            WHERE felhasznalok.felhasznalo_id = ?;", $felhasznalo_id);
        return $felhasznalo->EscapedArray()[0];
    }

    public static function GetFelhasznalok() : ?array {
        $felhasznalo = new MySQLHandler("SELECT usernev, email, teljesnev, fajl AS profilkep, felhasznalok.felhasznalo_id AS felhasznalo_id, szint, regisztracio
            FROM felhasznalok
                LEFT JOIN feltoltesek ON felhasznalok.profilkep = feltoltesek.feltoltes_id;");
        return $felhasznalo->EscapedArray();
    }
}