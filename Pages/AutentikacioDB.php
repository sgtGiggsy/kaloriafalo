<?php

namespace Kaloriafalo\Pages;
use Kaloriafalo\Classes\MySQLHandler;

class AutentikacioDB
{
    public static function FailedLoginNum(string $usernev) : int {
        $failed = new MySQLHandler("SELECT COUNT(*) AS kiserletszam
            FROM bejelentkezesi_hibak
            WHERE probalkozas_ideje > (NOW() - INTERVAL 10 MINUTE)
                AND (ip_cim = ? OR felhasznalo_nev = ?);", $_SERVER['REMOTE_ADDR'], $usernev);
        return $failed->Fetch()['kiserletszam'];
    }

    public static function LoginHandler(string $usernev) : ?array {
        $lhandler = new MySQLHandler('SELECT felhasznalo_id, jelszo FROM felhasznalok WHERE usernev = ?', $usernev);
        if($lhandler->sorokszama == 0)
            return null;
        else {
            $lhandler = $lhandler->Fetch();
            return ['felhasznalo_id' => $lhandler['felhasznalo_id'], 'jelszo' => $lhandler['jelszo']];
        }
    }

    public static function UsernevFromEmail(string $email) : ?string {
        $user = new MySQLHandler("SELECT usernev FROM felhasznalok WHERE email = ?;", $email);
        if($user->sorokszama == 1)
            return $user->Fetch()['usernev'];
        else
            return null;
    }

    public static function UsernameCheck(string $usernev) : bool {
        $check = new MySQLHandler("SELECT usernev FROM felhasznalok WHERE usernev = ?", $usernev);
        if($check->sorokszama == 0)
            return false;
        else
            return true;
    }

    public static function EmailCheck(string $email) : bool {
        $emailcheck = new MySQLHandler("SELECT email FROM felhasznalok WHERE email = ?", $email);
        if($emailcheck->sorokszama == 0)
            return false;
        else
            return true;
    }

    public static function GetJelszo(int $uid) : ?string {
        $regijelszo = new MySQLHandler('SELECT jelszo FROM felhasznalok WHERE felhasznalo_id = ?', $uid);
        return $regijelszo->Fetch()['jelszo'] ?? null;
    }

    public static function UserRegisztracio(string $usernev, string $email, string $hashedpassword, string $verifstring) : int {
        $newuser = new MySQLHandler("INSERT INTO felhasznalok (usernev, email, jelszo, allapot, verifstring, verifstring_hatarido)
            VALUES (?, ?, ?, ?, ?, NOW() + INTERVAL 1 HOUR)",
            $usernev, $email, $hashedpassword, 1, $verifstring);
        return $newuser->last_insert_id;
    }

    public static function ElfelejtettJelszo(string $verifstring, string $email) : bool {
        $ujjelszo = new MySQLHandler("UPDATE felhasznalok SET verifstring = ?, verifstring_hatarido = NOW() + INTERVAL 1 HOUR WHERE email = ?;", $verifstring, $email);
        return $ujjelszo->siker;
    }

    public static function ElfelejtettUj(string $hashedpassword, string $megerosit) : bool {
        $ujjelszo = new MySQLHandler("UPDATE felhasznalok SET jelszo = ?, verifstring = ?, verifstring_hatarido = ? WHERE verifstring = ?;", $hashedpassword, null, null, $megerosit);
        return $ujjelszo->siker;
    }

    public static function JelszoCsere(int $uid, string $hashedpassword) : bool {
        $jelszovaltas = new MySQLHandler('UPDATE felhasznalok SET jelszo = ?, jelszo_ido = current_timestamp() WHERE felhasznalo_id = ?;', $hashedpassword, $uid);
        return $jelszovaltas->siker;
    }

    public static function MegerositoKodCheck(string $megerositokod) : ?int {
        $osszevet = new MySQLHandler("SELECT felhasznalo_id FROM felhasznalok WHERE verifstring = ?;", $megerositokod);
        if($osszevet->sorokszama == 1)
            return $osszevet->Fetch()['felhasznalo_id'];
        else
            return null;
    }

    public static function UserMegerosit(int $felhasznalo_id) : bool {
        $meger = new MySQLHandler("UPDATE felhasznalok SET allapot = 2, verifstring = ? WHERE felhasznalo_id = ?;", null, $felhasznalo_id);
        return $meger->siker;
    }

    public static function ElfelejtettJelszoMegerosit(string $megerositokod) : bool {
        $osszevet = new MySQLHandler("SELECT felhasznalo_id FROM felhasznalok WHERE verifstring = ? AND verifstring_hatarido > NOW();", $megerositokod);
        if($osszevet->sorokszama == 1)
            return true;
        else
            return false;
    }
}