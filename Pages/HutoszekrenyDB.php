<?php

namespace Kaloriafalo\Pages;

use Kaloriafalo\Classes\MySQLHandler;

class HutoszekrenyDB
{
    public static function UjHutoszekreny(int $felhasznalo_id) : bool {
        $ujhuto = new MySQLHandler("INSERT INTO hutoszekrenyek (felhasznalo_id) VALUES (?);", $felhasznalo_id);
        return $ujhuto->siker;
    }

    private static function GetHutoTartalomHelper(int $huto_id) : array {
        $tartalom = new MySQLHandler("SELECT alapanyag_nev AS alapanyag, alapanyagok.alapanyag_id AS alapanyag_id
            FROM hutoszekreny_tartalmak
                INNER JOIN alapanyagok ON hutoszekreny_tartalmak.alapanyag_id = alapanyagok.alapanyag_id
            WHERE huto_id = ?;", $huto_id);
        return $tartalom->EscapedArray();
    }

    public static function GetHutoszekrenyByUser(int $felhasznalo_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE felhasznalo_id = ?;", $felhasznalo_id);
        $huto = $huto->EscapedArray()[0];
        $tartalom = self::GetHutoTartalomHelper($huto['huto_id']);
        return ['huto' => $huto, 'tartalom' => $tartalom];
    }

    public static function GetHutoszekrenyById(int $hutoszekreny_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE huto_id = ?;", $hutoszekreny_id);
        $huto = $huto->AsArray()[0];
        $tartalom = self::GetHutoTartalomHelper($huto['huto_id']);
        return ['huto' => $huto, 'tartalom' => $tartalom];
    }

    public static function GetHutoszekrenyByNev(int $hutoszekreny_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE huto_nev = ?;", $hutoszekreny_id);
        return $huto->EscapedArray()[0];
    }

    public static function GetHutoszekrenyek() : array {
        $hutok = new MySQLHandler("SELECT felhasznalok.usernev AS Felhasználó, huto_nev AS 'Hűtő neve', huto_id
            FROM hutoszekrenyek
                INNER JOIN felhasznalok ON hutoszekrenyek.felhasznalo_id = felhasznalok.felhasznalo_id
            ORDER BY felhasznalok.usernev;");
        return $hutok->EscapedArray();
    }

    public static function GetHutoIrasjog(int $felhasznalo_id, int $huto_id) : bool {
        $irasjog = new MySQLHandler("SELECT huto_id FROM hutoszekrenyek WHERE felhasznalo_id = ? AND huto_id = ?;", $felhasznalo_id, $huto_id);
        if($irasjog->sorokszama == 0)
            return false;
        else
            return true;
    }

    public static function Szerkeszt(int $huto_id, ?string $huto_nev) : bool {
        $szerkeszt = new MySQLHandler("UPDATE hutoszekrenyek SET huto_nev = ? WHERE huto_id = ?;", $huto_nev, $huto_id);
        return $szerkeszt->siker;
    }

    public static function TartalomSzerkeszt(int $huto_id, ?array $alapanyagok = null) : bool {
        $szerkeszt = new MySQLHandler();
        $szerkeszt->StartTransaction();
        $szerkeszt->Prepare("DELETE FROM hutoszekreny_tartalmak WHERE huto_id = ?;");
        $szerkeszt->Run($huto_id);
        $allapot = $szerkeszt->siker;
        $alapanyagok = array_filter($alapanyagok);
        $alapanyagszam = count($alapanyagok);
        if($alapanyagok && $allapot) {
            $prep = str_repeat('(?, ?),', $alapanyagszam - 1) . '(?, ?)';
            $szerkeszt->Prepare("INSERT INTO hutoszekreny_tartalmak (huto_id, alapanyag_id) VALUES " . $prep . ";");
            $alapanyagadatsorok = [];
            foreach($alapanyagok as $alapanyag) {
                $alapanyagadatsorok[] = $huto_id;
                $alapanyagadatsorok[] = $alapanyag;
            }
            $szerkeszt->Run(...$alapanyagadatsorok);
            $allapot = $szerkeszt->siker;
            $szerkeszt->ShowQueryDetails();
        }
        if($allapot)
            $szerkeszt->Commit();
        else
            $szerkeszt->Rollback();

        return $allapot;
    }
}