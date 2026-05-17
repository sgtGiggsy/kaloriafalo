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
        $huto = new MySQLHandler("SELECT huto_id, huto_nev, usernev
            FROM hutoszekrenyek
                INNER JOIN felhasznalok ON hutoszekrenyek.felhasznalo_id = felhasznalok.felhasznalo_id
            WHERE felhasznalok.felhasznalo_id = ?;", $felhasznalo_id);
        $huto = $huto->EscapedSingleElem();
        $tartalom = self::GetHutoTartalomHelper($huto['huto_id']);
        return ['huto' => $huto, 'tartalom' => $tartalom];
    }

    public static function GetHutoszekrenyById(int $hutoszekreny_id) : array {
        $huto = new MySQLHandler("SELECT huto_id, huto_nev, usernev
            FROM hutoszekrenyek
                INNER JOIN felhasznalok ON hutoszekrenyek.felhasznalo_id = felhasznalok.felhasznalo_id
            WHERE huto_id = ?;", $hutoszekreny_id);
        $huto = $huto->EscapedSingleElem();
        $tartalom = self::GetHutoTartalomHelper($huto['huto_id']);
        return ['huto' => $huto, 'tartalom' => $tartalom];
    }

    public static function GetHutoszekrenyByNev(int $hutoszekreny_id) : array {
        $huto = new MySQLHandler("SELECT * FROM hutoszekrenyek WHERE huto_nev = ?;", $hutoszekreny_id);
        return $huto->EscapedSingleElem();
    }

    public static function GetHutoszekrenyek(int $startindex = 0, int $dbszam = 20) : array {
        $hutok = new MySQLHandler("SELECT null AS '', felhasznalok.usernev AS Felhasználó, huto_nev AS 'Hűtő neve', huto_id
            FROM hutoszekrenyek
                INNER JOIN felhasznalok ON hutoszekrenyek.felhasznalo_id = felhasznalok.felhasznalo_id
            ORDER BY felhasznalok.usernev
            LIMIT ?, ?;", $startindex, $dbszam);
        return $hutok->EscapedArray();
    }

    public static function GetHutoIrasjog(int $felhasznalo_id, int $huto_id) : bool {
        $irasjog = new MySQLHandler("SELECT huto_id FROM hutoszekrenyek WHERE felhasznalo_id = ? AND huto_id = ?;", $felhasznalo_id, $huto_id);
        if($irasjog->sorokszama == 0)
            return false;
        else
            return true;
    }

    public static function ReceptlistByTartalom(int $huto_id) : array {
        $receptlista = new MySQLHandler("SELECT receptek.recept_id, receptek.recept_nev,
                    COUNT(recept_alapanyagok.alapanyag_id) AS osszes,
                    SUM(hutoszekreny_tartalmak.alapanyag_id IS NULL) AS hianyzo_db,
                    GROUP_CONCAT(
                        CASE WHEN hutoszekreny_tartalmak.alapanyag_id IS NULL THEN alapanyagok.alapanyag_nev END
                        SEPARATOR ', '
                    ) AS hianyzo_nevek,
                    GROUP_CONCAT(
                        CASE WHEN hutoszekreny_tartalmak.alapanyag_id IS NULL THEN alapanyagok.alapanyag_id END
                        SEPARATOR ', '
                    ) AS hianyzo_idk
                FROM receptek
                    JOIN recept_alapanyagok ON recept_alapanyagok.recept_id = receptek.recept_id
                    JOIN alapanyagok ON alapanyagok.alapanyag_id = recept_alapanyagok.alapanyag_id
                    LEFT JOIN hutoszekreny_tartalmak ON hutoszekreny_tartalmak.alapanyag_id = recept_alapanyagok.alapanyag_id AND hutoszekreny_tartalmak.huto_id = ?
                GROUP BY receptek.recept_id
                ORDER BY hianyzo_db ASC
                LIMIT 20;", $huto_id);
        $receptlista = $receptlista->EscapedArray($huto_id);
        foreach($receptlista as &$recept) {
            $hianyzo = [];
            $nevek = explode(', ', $recept['hianyzo_nevek']);
            $idk = explode(', ', $recept['hianyzo_idk']);
            $count = count($nevek);
            for($i = 0; $i < $count; $i++) {
                $hianyzo[] = ['alapanyag_nev' => $nevek[$i], 'alapanyag_id' => $idk[$i]];
            }
            $recept['hianyzo'] = $hianyzo;
        }
        return $receptlista;
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