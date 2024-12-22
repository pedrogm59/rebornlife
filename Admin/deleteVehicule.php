<?php
include '../init.php';
if (!Auth::isStaff()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous ne pouvez pas supprimer ce véhicule."];
    header('Location: /');
    exit();
}
$id = $_GET['id'];
$paneluser = $_SESSION['Auth']['username'];

if (isset($_GET) and isset($_GET['garage'])) {
    if ($_GET['garage'] == "Gendarmerie") {
        $search = $DB->prepare('SELECT * FROM vehicles_cop WHERE id = :id') or die(print_r($DB->errorInfo()));
        $search->bindParam(':id', $id);
        $search->execute();
        $vehicule = $search->fetch(PDO::FETCH_OBJ);
        if ($vehicule == NULL or empty($vehicule)) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => "Ce véhicule GN n'existe pas."];
            header('Location: /Gendarmerie/Garage.php');
            exit();
        }

        $DB->exec("DELETE FROM vehicles_cop WHERE id='$id'");
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule GN ($vehicule->classname) supprimé.')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule GN supprimé.'];
        header('Location: /Gendarmerie/Garage.php');
    }
    else if ($_GET['garage'] == "SapeursPompiers") {
        $search = $DB->prepare('SELECT * FROM vehicles_med WHERE id = :id') or die(print_r($DB->errorInfo()));
        $search->bindParam(':id', $id);
        $search->execute();
        $vehicule = $search->fetch(PDO::FETCH_OBJ);
        if ($vehicule == NULL or empty($vehicule)) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => "Ce véhicule SP n'existe pas."];
            header('Location: /SapeursPompiers/Garage.php');
        }

        $DB->exec("DELETE FROM vehicles_med WHERE id='$id'");
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule SP ($vehicule->classname) supprimé.')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule SP supprimé.'];
        header('Location: /SapeursPompiers/Garage.php');
    }
}
else {
    $search = $DB->prepare('SELECT * FROM vehicles JOIN players ON vehicles.pid = players.playerid WHERE vehicles.id = :id') or die(print_r($DB->errorInfo()));
    $search->bindParam(':id', $id);
    $search->execute();
    $vehicule = $search->fetch(PDO::FETCH_OBJ);
    if ($vehicule == NULL or empty($vehicule)) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => "Ce véhicule n'existe pas."];
        header('Location: /');
    }

    $DB->exec("DELETE FROM vehicles WHERE id='$id'");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule $vehicule->classname ($vehicule->name) supprimé.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule supprimé.'];
    header('Location: /joueur.php?id='.$vehicule->pid);
}
exit();