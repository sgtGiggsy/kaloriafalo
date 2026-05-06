<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;

class HutoszekrenyDB
{
    public static function UjHutoszekreny(int $felhasznalo_id) : bool {
        $ujhuto = new MySQLHandler("INSERT INTO hutoszekrenyek (felhasznalo_id) VALUES (?);", $felhasznalo_id);
        return $ujhuto->siker;
    }

    public static function GetHutoszekrenyByUser(int $felhasznalo_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE felhasznalo_id = ?;", $felhasznalo_id);
        $huto = $huto->AsArray()[0];
        $alapanyagok = new MySQLHandler("SELECT alapanyag_nev AS alapanyag, alapanyagok.slug
            FROM hutoszekreny_tartalmak
                INNER JOIN alapanyagok ON hutoszekreny_tartalmak.alapanyag_id = alapanyagok.alapanyag_id
            WHERE huto_id = ?;", $huto['huto_id']);
        return ['huto' => $huto, 'tartalom' => $alapanyagok->AsArray()];
    }

    public static function GetHutoszekrenyById(int $hutoszekreny_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE huto_id = ?;", $hutoszekreny_id);
        return $huto->AsArray()[0];
    }

    public static function GetHutoszekrenyByNev(int $hutoszekreny_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE huto_nev = ?;", $hutoszekreny_id);
        return $huto->AsArray()[0];
    }

    public static function GetHutoszekrenyek() : array {
        $hutok = new MySQLHandler("SELECT felhasznalok.usernev AS Felhasználó, huto_nev AS 'Hűtő neve', huto_id
            FROM hutoszekrenyek
                INNER JOIN felhasznalok ON hutoszekrenyek.felhasznalo_id = felhasznalok.felhasznalo_id
            ORDER BY felhasznalok.usernev;");
        return $hutok->AsArray();
    }

    public static function GetHutoIrasjog(int $felhasznalo_id, int $huto_id) : bool {
        $irasjog = new MySQLHandler("SELECT huto_id FROM hutoszekrenyek WHERE felhasznalo_id = ? AND huto_id = ?;", $felhasznalo_id, $huto_id);
        if($irasjog->sorokszama == 0)
            return false;
        else
            return true;
    }
}