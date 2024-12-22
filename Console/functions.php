<?php

$functions = [
    "function_ip"   => "Renvoit l'IP.",
    "function_cat"  => "Affiche le contenu d'un fichier.",
    "function_cd"   => "Permet de se déplacer dans les dossiers.",
    "function_ls"   => "Affiche le contenu du dossier.",
    "function_echo" => "Renvoit le même texte que celui envoyé.",
];

$files = [
    "" => [
        "GN"    => [
            "test.txt"    => "Je pense donc je suis.",
            "Effectif"    => [],
            "TAJ"         => [],
            "clearFPR.sh" => "wget --method=POST https://intranet.arma3lifefrance.fr/CelluleRens/clearFPR",
        ],
        "SDIS"  => [
            "Fiches-Bilans" => [],
        ],
        "ghjkl" => "TEST",
    ],
];

function function_echo($command) {
    $a = explode(" ", $command);
    $s = "";
    for ($i = 1; $i < count($a); $i++) $s .= $a[$i];

    return $s;
}

function function_ip() {
    return $_SERVER["REMOTE_ADDR"];
}

function function_cat($command, $path) {
    $a = explode(" ", $command);
    if (count($a) == 1) return "";
    else if ($a[1][0] == "/") $dir = $a[1];
    else if ($path == "/") $dir = $path.$a[1];
    else $dir = $path."/".$a[1];

    $file = getFile($dir);
    if ($file === NULL) return "cat: '$dir': No such file";
    else if (is_array($file)) return "cat: '$dir': Is a directory";
    else return $file;
}

function function_cd($command, $path) {
    $a = explode(" ", $command);
    if (count($a) == 1 or $a[1] == "." or $a[1] == "") return "";
    else if ($a[1] == "..") {
        $f = explode("/", $path);
        if (count($f) == 1) $dir = "/";
        else if (count($f) == 2) $dir = "/".$f[0];
        else {
            array_pop($f);
            $dir = join("/", $f);
        }
    }
    else if ($a[1][0] == "/") $dir = $a[1];
    else if ($path == "/") $dir = $path.$a[1];
    else $dir = $path."/".$a[1];
    if ($dir != "/") $dir = rtrim($dir, "/");

    $file = getFile($dir);
    if ($file === NULL) return "cd: '$dir': No such directory";
    else if (!is_array($file)) return "cd: '$dir': Not a directory";
    else return ["", $dir];
}

function function_ls($command, $path) {
    $a = explode(" ", $command);
    if (count($a) >= 2) {
        if ($a[1][0] == "/") $dir = $a[1];
        else if ($path == "/") $dir = $path.$a[1];
        else $dir = $path."/".$a[1];
    }
    else $dir = $path;
    if ($dir != "/") $dir = rtrim($dir, "/");

    $file = getFile($dir);
    if ($file === NULL) return "ls: cannot access '$dir': No such file or directory";
    else if (!is_array($file)) return $dir;
    else return join("  ", array_keys($file));
}

//-----------------------------------------------------------------
function getFile($name) {
    function getFileR($name, $path, $f) {
        if (!is_array($f)) return NULL;
        foreach ($f as $key => $value) {
            if ($path."/".$key == $name) return $value;
            else if (is_array($value)) {
                $file = getFileR($name, $key == "" ? $path : $path."/".$key, $value);
                if ($file !== NULL) return $file;
            }
        }

        return NULL;
    }

    global $files;

    return getFileR($name, "", $files);
}