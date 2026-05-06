<?php

namespace Kaloriafalo\Classes;

class Settings
{
    public static bool $foadmin = false;
    public static bool $admin = false;
    public static ?int $uid = null;
    public static int $session_id;
    public static ?string $usernev = null;
    public static ?string $profilkep = null;
    public static ?string $nonce = null;
    public static ?string $slug = null;
    public static array $jsfiles = array();
    public static array $PHPvarsToJS = array();
}