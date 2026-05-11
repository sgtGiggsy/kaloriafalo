<?php

namespace Kaloriafalo\Classes;

class FeltoltesHandler
{
    private array $mediatypes;
    private bool $eleresiut = false;
    private string $tipus;
    private int $uid;
    private MySQLHandler $sql;

    public function __construct(array $mediatypes, string $gyokermappa, string $egyedimappa, string $tipus, int $uid)
    {
        $this->mediatypes = $mediatypes;
        $this->sql = new MySQLHandler();
        $this->sql->Prepare("INSERT INTO feltoltesek (fajl, felhasznalo_id, tipus) VALUES (?, ?, ?)");
        $this->sql->StartTransaction();
        $this->uid = $uid;
        $this->eleresiut = $this->SetPath($gyokermappa, $egyedimappa);
        $this->tipus = $tipus;
    }
    public  function Feltoltes($fajlok) : array|bool {
        $masoltfajlok = array(); $hibalista = array(); $uploadids = array();
        $sikeresfeltoltes = false;

        $fajlok = $this->FajlTombNormalizalas($fajlok);

        foreach($fajlok as $fajl) {
            $fajlvalidate = $this->ValidateFajl($fajl, $this->mediatypes);
            $hibalista[] = $fajlvalidate['hiba'];

            if($fajlvalidate['eredmeny'] === true) {
                $ext = strtolower(pathinfo($fajl['name'], PATHINFO_EXTENSION));
                $name = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($fajl['name'], PATHINFO_FILENAME)));
                $ujfajlnev = $name . bin2hex(random_bytes(8)) . '.' . $ext;

                $finalfile = $this->eleresiut . $ujfajlnev;
                try {
                    move_uploaded_file($fajl['tmp_name'], $finalfile);
                }
                catch (\Exception $e) {
                    $hibalista[] = $e->getMessage();
                }

                if(!file_exists($finalfile))
                    $sikeresfeltoltes = false;
                else {
                    $this->sql->Run($finalfile, $this->uid, $this->tipus);
                    $uploadids[] = $this->sql->last_insert_id;
                    $masoltfajlok[] = $finalfile;
                    $sikeresfeltoltes = true;
                }

            }
            if(!$sikeresfeltoltes) {
                break;
            }
        }

        if(!$sikeresfeltoltes) {
            $this->sql->Rollback();
            foreach($masoltfajlok as $fajl) {
                unlink($fajl);
            }
        }
        else
            $this->sql->Commit();

        return ['eredmeny' => $sikeresfeltoltes, 'hibalista' => $hibalista, 'uploadids' => $uploadids];
    }

    private function FajlTombNormalizalas (array $files): array {
        $result = [];

        if(!is_array($files['name'])) {
            $fajlok[] = $files;
        } else {
            foreach ($files['name'] as $i => $name) {
                $result[$i] = [
                    'name' => $name,
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
            }
        }

        return $result;
    }

    private function SetPath(string $gyokermappa, string $egyedimappa) : string|bool {
        if(Helpers::StrContainsAny($egyedimappa, '..', ':', '*', '?', '"', '<', '>', '|') ||
            Helpers::StrContainsAny($gyokermappa, '..', '/', '\\', ':', '*', '?', '"', '<', '>', '|'))
            return false;

        $feltoltesimappa = $GLOBALS['UPLOAD_FOLDER'] . $gyokermappa . '/' . $egyedimappa . '/';

        if(!file_exists($feltoltesimappa)) {
            mkdir($feltoltesimappa, 0755, true);
        }

        $baseDir = rtrim(realpath($GLOBALS['UPLOAD_FOLDER']), '/') . '/';
        $target = rtrim(realpath($feltoltesimappa), '/') . '/';

        if (!str_starts_with($target, $baseDir))
            return false;

        return $feltoltesimappa;
    }

    private function ValidateFajl($fajl, $mediatypes) : array {
        $valid = true;
        $hiba = "";
        if($fajl['error'] !== UPLOAD_ERR_OK) {
            $hiba = 'Feltöltési hiba a ' . $fajl['name'] . ' fájlban: ' . $fajl['error'];
            $valid = false;
        }

        elseif(!in_array($fajl['type'], $mediatypes)) {
            $hiba = "A fájl típusa nem megengedett: " . $fajl['name'] . " A feltöltött fájl típusa: " . $fajl['type'];
            $valid = false;
        }

        else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fajl['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $mediatypes, true)) {
                $hiba = "A fájl típusa nem megengedett: " . $fajl['name'] . " A feltöltött MIME-ja: " . $mime;
                $valid = false;
            }

            elseif(str_starts_with($mime, 'image/')) {
                if (!getimagesize($fajl['tmp_name'])) {
                    $hiba = "A(z) " . $fajl['name'] . " fájl nem ismerhető fel valódi képként!";
                    $valid = false;
                }
            }

            elseif($mime == 'application/pdf') {
                $fh = fopen($fajl['tmp_name'], 'rb');
                $header = fread($fh, 4);
                fclose($fh);

                if ($header !== '%PDF') {
                    $hiba = "A(z) " . $fajl['name'] . " fájl nem ismerhető fel valódi PDF-ként!";
                    $valid = false;
                }
            }
        }

        return ['eredmeny' => $valid, 'hiba' => $hiba];
    }

}