<?php
session_start();
require_once "variables.php";

class Auth {
    static function isLogged() {
        if (isset($_SESSION['Auth']['id']) and isset($_SESSION['Auth']['username']) and isset($_SESSION['Auth']['password']) and isset($_SESSION['Auth']['role'])) return TRUE;
        else return FALSE;
    }

    private static function isLevel($i) {
        if (isset($_SESSION['Auth']['id']) and isset($_SESSION['Auth']['username']) and isset($_SESSION['Auth']['password']) and isset($_SESSION['Auth']['role'])
            and $_SESSION['Auth']['role'] >= $i) return TRUE;
        else return FALSE;
    }

    static function isAdmin() {
        return Auth::isLevel(4);
    }

    static function isStaff() {
        return Auth::isLevel(3);
    }

    static function isModo() {
        return Auth::isLevel(2);
    }

    static function isOperateur() {
        return Auth::isLevel(1);
    }

    static function getRole() {
        $roles = ['Opérateur', 'Staff 1', 'Staff 2', 'Admin'];

        return $roles[$_SESSION['Auth']['role'] - 1];
    }

    static function hasRCon() {
        return $_SESSION['Auth']['rcon'];
    }
}

function pad($str) {
    $str = strval($str);

    return strlen($str) < 3 ? pad("0".$str) : $str;
}

try {
    $DB = new PDO('mysql:host='.$ip.';dbname='.$bdd.';charset=UTF8', $user, $passwd);
} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
define('__ROOT__', dirname(__FILE__));
if (!Auth::isLogged() and $_SERVER['SCRIPT_NAME'] != "/login.php" and $_SERVER['SCRIPT_NAME'] != "/API.php" and $_SERVER['SCRIPT_NAME'] != "/xBaoshj.php") {
    header('Location: /login.php');
    exit();
}
$gradesGN = ['', 'ELG', 'GND', 'MDC', 'ADJ', 'ADC', 'MAJ', 'SLT', 'LTN', 'CNE', 'CDT', 'LCL', 'COL'];
$gradesPompier = ['', 'SAP', '1CL', 'CPL', 'CCH', 'SGT', 'SCH', 'ADJ', 'ADC', 'LTN', 'CNE', 'CDT', 'COL'];
$gradesSP = ['', 'LV1', 'LV2', 'LV3', 'LV4', 'LV5', 'LV6', 'LV7', 'LV8'];
$gradesAP = ['', 'ES', 'SRV', 'SP', 'SB', 'PS', 'MAJ', 'LTN', 'CNE', 'CMD', 'DSP'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);