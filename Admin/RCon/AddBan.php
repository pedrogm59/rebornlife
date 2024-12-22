<?php

use Nizarii\ARC;

require_once '../../init.php';
require_once 'ARC.php';

if (!Auth::hasRCon()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès au RCon."];
    header('Location: /');
    exit();
}

try {
    $rcon = new ARC($ipRCon, $passwdRCon, $portRCon, [
        'timeoutSec' => 2,
        'debug'      => FALSE,
    ]);

    if (isset($_POST) and isset($_POST['reasonBan']) and isset($_POST['time']) and isset($_POST['guid'])) {
        $paneluser = $_SESSION['Auth']['username'];
        $guid = $_POST['guid'];
        $time = $_POST['time'];
        $message = $time == '0' ? "perm" : $time." jours";
        $reason = $_POST['reasonBan'].' - '.$message.' - '.$paneluser.' - '.date('d/m/y H:i');

        $rcon->addBan($guid, $reason, $time * 24 * 60);
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','AddBan ($guid - $reason).')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => "Le ban a bien été ajouté."];
        header('Location: /Admin/RCon/Liste.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => "Erreur (".$e->getMessage().")."];
    header('Location: /');
    exit();
}

require_once '../../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Bans</h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <h2>Nouveau ban par GUID</h2>
            <form method="post" onsubmit="return confirm('Confirmer le ban : ');">
                <div class="form-group">
                    <label for="guid">GUID</label>
                    <input type="text" title="GUID à bannir" name="guid" id="guid" required class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="reasonBan">Raison</label>
                    <input type="text" title="Raison du ban" name="reasonBan" id="reasonBan" required class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="time">Durée en jours <em>(Mettre 0 pour un ban permanent)</em></label>
                    <input type="number" step="1" min="0" title="Durée du ban" name="time" id="time" required class="form-control"/
                </div>
                <button type="submit" class="btn btn-primary mt-5">Ajouter</button>
            </form>
        </div>
    </div>
<?php
require_once '../../templates/bottom.html';
