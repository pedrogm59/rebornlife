<?php
require_once('init.php');
$resPlayer = $DB->prepare('SELECT * FROM players WHERE playerid = :playerid') or die(print_r($DB->errorInfo()));
$resPlayer->bindParam(':playerid', $_GET['id']);
$resPlayer->execute();
$player = $resPlayer->fetch(PDO::FETCH_OBJ);

$resBank = $DB->prepare('SELECT * FROM bank WHERE playerid = :playerid') or die(print_r($DB->errorInfo()));
$resBank->bindParam(':playerid', $_GET['id']);
$resBank->execute();
$banq = $resBank->fetch(PDO::FETCH_OBJ);

$resPhone = $DB->prepare('SELECT * FROM phone WHERE playerid = :playerid') or die(print_r($DB->errorInfo()));
$resPhone->bindParam(':playerid', $_GET['id']);
$resPhone->execute();
$phone = $resPhone->fetch(PDO::FETCH_OBJ);

if (empty($player)) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Ce joueur n'existe pas."];
    header('Location: /');
    exit();
}

if (isset($_POST) and isset($_POST['name']) and isset($_POST['message']) and $_POST['message'] != "") //Modification JOUEUR
{
    $playerid = $_GET['id'];
    $name = $_POST['name'];
    $oldname = $_POST['oldname'];
    $message = $_POST['message'];
    $position = $_POST['position'];
    $oldPosition = $_POST['oldpos'];
    $isValidate = $_POST['isValidate'];
    $oldValidate = $_POST['oldwl'];
    if (Auth::isModo()) {
        $cash = $_POST['cash'];
        $licenses = $_POST['licenses'];
        $coplevel = $_POST['coplevel'];
        $mediclevel = $_POST['mediclevel'];
        $publique = $_POST['publique'];
        $penit = $_POST['penit'];
        $alive = $_POST['alive'];
        $oldalive = $_POST['oldlife'];
        if ($banq != NULL) {
            $livreta = $_POST['livreta'];
            $livretb = $_POST['livretb'];
            $livretc = $_POST['livretc'];
            $oldcash = $_POST['oldcash'];
            $oldlivreta = $_POST['oldlivreta'];
            $oldlivretb = $_POST['oldlivretb'];
            $oldlivretc = $_POST['oldlivretc'];
        }
    }
    if (Auth::isStaff()) $gear = $_POST['gear'];
    $paneluser = $_SESSION['Auth']['username'];

    if (Auth::isAdmin()) {
        $isPremium = $_POST['isPremium'];
        $duredon = $_POST['duredon'];
        $adminlevel = $_POST['adminlevel'];
        $oldadminlevel = $_POST['oldadminlevel'];
        $isABS = $_POST['isABS'];

        $sqlupdate = "UPDATE players SET name='$name', cash='$cash', adminlevel='$adminlevel', isPremium='$isPremium', duredon='$duredon', alive='$alive', penit='$penit', publique='$publique', mediclevel='$mediclevel', coplevel='$coplevel', gear='$gear', position='$position', licenses='$licenses', isValidate='$isValidate', isABS='$isABS' WHERE playerid='$playerid'";
    }
    else if (Auth::isStaff())
        $sqlupdate = "UPDATE players SET name='$name', cash='$cash', alive='$alive', penit='$penit', publique='$publique', mediclevel='$mediclevel', coplevel='$coplevel', gear='$gear', position='$position', licenses='$licenses', isValidate='$isValidate' WHERE playerid='$playerid'";
    else if (Auth::isModo())
        $sqlupdate = "UPDATE players SET name='$name', cash='$cash', alive='$alive', penit='$penit', publique='$publique', mediclevel='$mediclevel', coplevel='$coplevel', position='$position', licenses='$licenses', isValidate='$isValidate' WHERE playerid='$playerid'";
    else
        $sqlupdate = "UPDATE players SET name='$name', position='$position', isValidate='$isValidate' WHERE playerid='$playerid'";
    $DB->exec($sqlupdate);

    $txt = "";

    if ($oldname != $name) {
        $txt = $txt."<br>Nom RP : $oldname -> $name";
    }

    if ($oldValidate != $isValidate) {
        if ($isValidate == 0) {
            $txt = $txt."<br>Joueur dé-whitelist";
        }
        else if ($isValidate == 2) {
            $txt = $txt."<br>Visa bloqué";
        }
        else {
            $txt = $txt."<br>Visa validé";
        }
    }

    if ($oldadminlevel != $adminlevel) {
        $txt = $txt."<br>Admin Level : $oldadminlevel -> $adminlevel";
    }

    if ($oldalive != $alive) {
        $txt = $txt."<br>Joueur vivant : $oldalive -> $alive";
    }

    if ('"'.$oldPosition.'"' != $position) {
        $txt = $txt."<br>Position : $oldPosition -> $position";
    }

    if ($banq != NULL) {
        if ($oldcash != $cash OR $oldlivreta != $livreta OR $oldlivretb != $livretb OR $oldlivretc != $livretc) {
            $DB->exec("UPDATE bank SET livreta='$livreta', livretb='$livretb', livretc='$livretc' WHERE playerid='$playerid'");
            $txt = $txt."<br>CASH : $oldcash -> $cash / LIVRETA : $oldlivreta -> $livreta / LIVRETB : $oldlivretb -> $livretb / LIVRETC : $oldlivretc -> $livretc";
        }
    }
    $message = addslashes($message);
    $DB->exec("INSERT INTO logs_panel (username, texte, pid) VALUES ('$paneluser','Joueur ($name) modifié [$message] $txt','$playerid')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Joueur modifié et sauvegardé !'];

    header('Location: /joueur.php?id='.$playerid);
    exit();
}
else if (isset($_GET) and isset($_GET['setDonateur']) and Auth::isAdmin()) //Ajout donateur (set 31 jours)
{
    $paneluser = $_SESSION['Auth']['username'];
    $playerid = $_GET['id'];
    $name = $player->name;
    $DB->exec("UPDATE players SET duredon=31, isPremium=1 WHERE playerid='$playerid';");
    $DB->exec("INSERT INTO logs_panel (username, texte, pid) VALUES ('$paneluser','Joueur ($name) passé en donateur.','$playerid')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Joueur passé en donateur à 31 jours.'];
    header('Location: /joueur.php?id='.$playerid);
    exit();
}
else if (isset($_GET) and isset($_GET['delete']) and Auth::isStaff()) //Delete JOUEUR
{
    $id = $_GET['id'];
    try {
        $DB->exec("DELETE FROM mobiliers WHERE pid = '$id'");
        $DB->exec("DELETE FROM vehicles WHERE pid = '$id'");
        $DB->exec("DELETE FROM keystime WHERE pid = '$id'");
        $DB->exec("DELETE FROM houses WHERE pid = '$id'");
        $DB->exec("DELETE FROM bank WHERE playerid = '$id'");
        $DB->exec("DELETE FROM sms WHERE expediteur = '$id'");
        $DB->exec("DELETE FROM phone WHERE playerid = '$id'");
        $DB->exec("DELETE FROM players WHERE playerid = '$id'");
    } catch (PDOException $e) {
        die('Erreur lors de la suppression : '.$e->getMessage());
    }

    $paneluser = $_SESSION['Auth']['username'];
    $DB->exec("INSERT INTO logs_panel (username, texte, pid) VALUES ('$paneluser','Joueur ($player->name) supprimé.','$id')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Joueur supprimé.'];
    header('Location: /');
    exit();
}
else if (isset($_GET) and isset($_GET['maison']) and isset($_GET['pos']) and isset($_GET['deleteMaison']) and Auth::isModo()) //Delete MAISON
{
    $idMaison = $_GET['maison'];
    $paneluser = $_SESSION['Auth']['username'];
    $playerid = $_GET['id'];
    $pos = $_GET['pos'];
    $name = $player->name;
    $DB->exec("DELETE FROM houses WHERE id=$idMaison;");
    $DB->exec("INSERT INTO logs_panel (username, texte, pid) VALUES ('$paneluser','Maison de $name ($pos) supprimée.','$playerid')");
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Maison supprimée.'];
    header('Location: /joueur.php?id='.$playerid);
    exit();
}
else if (isset($_GET) and isset($_GET['vehicule']) and isset($_GET['sendHome'])) //Renvoi véhicule au garage
{
    $idVehicule = $_GET['vehicule'];
    $paneluser = $_SESSION['Auth']['username'];
    $playerid = $_GET['id'];
    $name = $player->name;
    $DB->exec("UPDATE vehicles SET active=0 WHERE id=$idVehicule;");
    $DB->exec("INSERT INTO logs_panel (username, texte, pid) VALUES ('$paneluser','Véhicule de $name ($idVehicule) renvoyé au garage.','$playerid')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule renvoyé au garage (au prochain reboot).'];
    header('Location: /joueur.php?id='.$playerid);
    exit();
}
else if (isset($_POST) and isset($_GET['addVehicule']) and Auth::isAdmin()) //Ajout véhicule
{
    $plaqueClean = $_POST['plaque'];
    $classname = $_POST['classname'];
    $raison = str_replace("'", "\'", $_POST['message']);
    $plaque = '"[';
    $type = $_POST['type'];
    $paneluser = $_SESSION['Auth']['username'];
    $name = $player->name;
    $insertTime = (new DateTime)->format('Y-m-d H:i:s');
    for ($i = 0; $i < 8; $i++)
        $plaque .= '`'.$plaqueClean[$i].'`,';

    $plaque .= '`'.$plaqueClean[8].'`]"';

    try {
        $res = $DB->prepare('SELECT id FROM shop WHERE classname = :classname') or die(print_r($DB->errorInfo()));
        $res->bindParam(':classname', $classname);
        $res->execute();
        if (!isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Ce véhicule n'existe pas dans le shop."];
            header('Location: /joueur.php?id='.$player->playerid);
        }
        $res->closeCursor();

        $res = $DB->prepare('SELECT id FROM vehicles WHERE plate = :plaque') or die(print_r($DB->errorInfo()));
        $res->bindParam(':plaque', $plaque);
        $res->execute();
        if (isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Cette plaque existe déjà."];
            header('Location: /joueur.php?id='.$player->playerid);
            exit();
        }
        $res->closeCursor();

        $DB->exec("INSERT INTO vehicles (classname, type, pid, alive, active, plate, gear, position, pos_save, pos_check, fuel, damage, insure, insurecount, insuretime, isInHome, lockveh, locktime, insert_time) 
                                      VALUES ('$classname', '$type', $player->playerid, 1, 0, '$plaque', '[]', '[]', '[]', '[]', 1, '[]', 0, 0, 0, 0, 0, 0, '$insertTime');");
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule de $name ($classname) ajouté au garage. [$raison]')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule ajouté au garage.'];
        header('Location: /joueur.php?id='.$player->playerid);
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Erreur : '.$e->getMessage()];
        header('Location: /joueur.php?id='.$player->playerid);
    }
}
else if (isset($_POST) and isset($_GET['editVehicule']) and Auth::isModo()) //Modification véhicule
{
    $idVehicule = $_GET['editVehicule'];
    $res = $DB->prepare('SELECT classname FROM vehicles WHERE id=:id') or die(print_r($DB->errorInfo()));
    $res->bindParam(':id', $idVehicule);
    $res->execute();
    $classname = $res->fetch(PDO::FETCH_ASSOC)['classname'];
    if (!isset($classname)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Ce véhicule n'existe pas."];
        header('Location: /joueur.php?id='.$player->playerid);
    }
    $raison = str_replace("'", "\'", $_POST['message']);
    $pid = isset($_POST['pid']) ? $_POST['pid'] : $player->playerid;
    $peinture = '"'.$_POST['peinture'].'"';
    $fuel = $_POST['fuel'];
    $plaqueClean = $_POST['plate'];
    $insure = $_POST['insure'];
    $insurecount = $_POST['insurecount'];
    $type = $_POST['typeEdit'];
    $classname = $_POST['classnameEdit'];
    $paneluser = $_SESSION['Auth']['username'];
    $name = $player->name;
    $plaque = '"[';
    for ($i = 0; $i < 8; $i++)
        $plaque .= '`'.$plaqueClean[$i].'`,';
    $plaque .= '`'.$plaqueClean[8].'`]"';

    try {
        $res = $DB->prepare('SELECT id FROM shop WHERE classname = :classname') or die(print_r($DB->errorInfo()));
        $res->bindParam(':classname', $classname);
        $res->execute();
        if (!isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Ce véhicule n'existe pas dans le shop."];
            header('Location: /joueur.php?id='.$player->playerid);
        }
        $res->closeCursor();

        $res = $DB->prepare('SELECT id FROM vehicles WHERE plate = :plaque AND id != :id') or die(print_r($DB->errorInfo()));
        $res->bindParam(':plaque', $plaque);
        $res->bindParam(':id', $idVehicule);
        $res->execute();
        if (isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Cette plaque existe déjà."];
            header('Location: /joueur.php?id='.$player->playerid);
            exit();
        }
        $res->closeCursor();

        if (Auth::isAdmin())
            $DB->exec("UPDATE vehicles SET classname='$classname', type='$type', pid = '$pid', plate = '$plaque', peinture = '$peinture', fuel='$fuel', insure = '$insure', insurecount='$insurecount' WHERE id = '$idVehicule'");
        else
            $DB->exec("UPDATE vehicles SET fuel='$fuel', insure = '$insure', insurecount='$insurecount' WHERE id = '$idVehicule'");

        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule de $name ($classname) modifié. [$raison]')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule modifié.'];
        header('Location: /joueur.php?id='.$player->playerid);
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Erreur : '.$e->getMessage()];
        header('Location: /joueur.php?id='.$player->playerid);
    }
}

$vehicules = $DB->prepare('SELECT * FROM vehicles WHERE pid = :playerid ORDER BY type ASC, classname ASC') or die(print_r($DB->errorInfo()));
$vehicules->bindParam(':playerid', $_GET['id']);
$vehicules->execute();

$houses = $DB->prepare('SELECT * FROM houses WHERE pid = :playerid') or die(print_r($DB->errorInfo()));
$houses->bindParam(':playerid', $_GET['id']);
$houses->execute();

$logs = $DB->prepare("SELECT * FROM logs_alf WHERE uid = :playerid AND type != 'ADMIN' AND type != 'SERVICE' ORDER BY insert_time DESC LIMIT 200") or die(print_r($DB->errorInfo()));
$logs->bindParam(':playerid', $_GET['id']);
$logs->execute();

if ($player->coplevel > 0) {
    $service = $DB->prepare('SELECT * FROM copservice WHERE name = :playername ORDER BY insert_time ASC') or die(print_r($DB->errorInfo()));
    $service->bindParam(':playername', $player->name);
    $service->execute();
}
else if ($player->mediclevel > 0) {
    $service = $DB->prepare('SELECT * FROM medservice WHERE name = :playername ORDER BY insert_time ASC') or die(print_r($DB->errorInfo()));
    $service->bindParam(':playername', $player->name);
    $service->execute();
}
else if ($player->publique > 0 or $player->penit > 0) {
    $service = $DB->prepare("SELECT * FROM logs_alf WHERE type = 'SERVICE' AND name = :playername ORDER BY insert_time ASC") or die(print_r($DB->errorInfo()));
    $service->bindParam(':playername', $player->name);
    $service->execute();
}
$message = "%".$player->name."%";
$logsPanel = $DB->prepare('SELECT * FROM logs_panel WHERE texte LIKE :nom OR pid =:pid ORDER BY datetime DESC LIMIT 50') or die(print_r($DB->errorInfo()));
$logsPanel->bindParam(':nom', $message);
$logsPanel->bindParam(':pid', $player->playerid);
$logsPanel->execute();

function convert_seconds($seconds) {
    $dt1 = new DateTime("@0");
    $dt2 = new DateTime("@$seconds");

    return $dt1->diff($dt2)->format('%a jours %h heures %i minutes');
}

function getGear($gear) {
    $gear = explode(' ', $gear);
    sort($gear);

    $current = NULL;
    $txt = "";
    $cnt = 0;

    if ($gear[0] != NULL) {
        for ($i = 0; $i < count($gear); $i++) {
            if ($gear[$i] != $current) {
                if ($cnt > 0) {
                    $txt = $txt.$current.' [x'.$cnt.']<br>';
                }
                $current = $gear[$i];
                $cnt = 1;
            }
            else {
                $cnt++;
            }
        }
        if ($cnt > 0) {
            $txt = $txt.$current.' [x'.$cnt.']';
        }
    }
    else {
        $txt = "Vide";
    }

    return $txt;
}

require_once('templates/top.php'); ?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4"><?= $player->name ?></h2>
        </div>
    </header>
    <form method="post" autocomplete="off">
        <div class="row">
            <div class="col">
                <div class="float-right mr-5">
                    <div class="form-group input-group m-3 input-group-lg">
                        <div class="input-group-prepend input-group-text"><strong>Message</strong></div>
                        <input title="Raison de la modification" name="message" placeholder="Raison de la modification" required class="form-control"/>
                        <div class="input-group-append">
                            <button onclick="return confirm('Modifier le joueur : ');" type="submit" class="btn btn-outline-success">
                                <span class="fa fa-edit"></span> Modifier
                            </button>
                            <?php if (Auth::isStaff()) { ?>
                                <a href="/joueur.php?id=<?= $_GET['id'] ?>&delete" onclick="return confirm('Supprimer le joueur : ');" class="btn btn-outline-danger">
                                    <span class="fa fa-trash"></span> Supprimer
                                </a>
                            <?php } ?>
                            <?php if (Auth::isAdmin()) { ?>
                                <a href="/joueur.php?id=<?= $_GET['id'] ?>&setDonateur" onclick="return confirm('Confirmer l\'ajout du statut de donateur: ');" class="btn
                                btn-outline-warning">
                                    <span class="fa fa-dollar-sign"></span> Donateur
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Informations</h5>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><strong>User ID :</strong> <?= $player->uid ?></li>
                            <li><strong>Steam ID 64 :</strong> <?= $player->playerid; ?></li>
                            <li><strong>BE GUID :</strong> <span id="GUID"></span></li>
                            <li><strong>A déjà joué :</strong> <?php if ($player->firstSpawn == 0) echo ' Oui'; else echo " Non" ?></li>
                            <li><strong>A joué il y a :</strong> <?= $player->lastPlayed; ?> jours</li>
                            <li><strong>Temps de jeu :</strong> <?= convert_seconds($player->playtime * 60); ?> (<?= number_format($player->playtime / 60, 0, ",", "."); ?> heures)
                            </li>
                        </ul>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Nom</strong><input type="hidden" name="oldname" value="<?= $player->name; ?>"></div>
                            <input title="Nom" type="text" name="name" value="<?= $player->name; ?>" class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-user"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Whitelist</strong><input type="hidden" name="oldwl" value="<?= $player->isValidate; ?>"></div>
                            <select title="Whitelist" id="isValidate" name="isValidate" class="form-control classicSelect">
                                <option value="0" <?php if ($player->isValidate == 0) echo 'selected = "selected"'; ?>>Non</option>
                                <option value="1" <?php if ($player->isValidate == 1) echo 'selected = "selected"'; ?>>Oui</option>
                                <option value="2" <?php if ($player->isValidate == 2) echo 'selected = "selected"'; ?>>Bloqué</option>
                            </select>
                            <div class="input-group-append input-group-text"><span class="fa fa-check"></span></div>
                        </div>
                        <?php if (Auth::isModo()) { ?>
                            <?php if (Auth::isAdmin()) { ?>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Absent</strong></div>
                                    <select title="Absence" name="isABS" class="form-control classicSelect">
                                        <option value="0" <?php if ($player->isABS == 0) echo 'selected = "selected"'; ?>>Non</option>
                                        <option value="1" <?php if ($player->isABS == 1) echo 'selected = "selected"'; ?>>Oui</option>
                                    </select>
                                    <div class="input-group-append input-group-text"><span class="fa fa-notes-medical"></span></div>
                                </div>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Premium</strong></div>
                                    <select title="Premium" style="height:40px;" name="isPremium" class="form-control classicSelect">
                                        <option value="0" <?php if ($player->isPremium == 0) echo 'selected = "selected"'; ?>>Non</option>
                                        <option value="1" <?php if ($player->isPremium == 1) echo 'selected = "selected"'; ?>>Oui</option>
                                    </select>
                                    <div class="input-group-append input-group-text"><span class="fa fa-money-bill"></span></div>
                                </div>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Durée Premium</strong></div>
                                    <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Durée Premium" type="text" name="duredon" value="<?= $player->duredon; ?>"
                                           class="form-control">
                                    <div class="input-group-append input-group-text"><span class="fa fa-business-time"></span></div>
                                </div>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Admin IG</strong><input type="hidden" name="oldadminlevel"
                                                                                                                      value="<?= $player->adminlevel; ?>"></div>
                                    <select title="Admin IG" name="adminlevel" class="form-control classicSelect">
                                        <option value="0" <?php if ($player->adminlevel == 0) echo 'selected = "selected"'; ?>>Non</option>
                                        <option value="1" <?php if ($player->adminlevel == 1) echo 'selected = "selected"'; ?>>1 - Staff</option>
                                        <option value="2" <?php if ($player->adminlevel == 2) echo 'selected = "selected"'; ?>>2 - Staff</option>
                                        <option value="3" <?php if ($player->adminlevel == 3) echo 'selected = "selected"'; ?>>3 - Admin</option>
                                    </select>
                                    <div class="input-group-append input-group-text"><span class="fa fa-toolbox"></span></div>
                                </div>
                            <?php } ?>
                            <div class="form-group input-group">
                                <div class="input-group-prepend input-group-text"><strong>En vie</strong><input type="hidden" name="oldlife" value="<?= $player->alive; ?>"></div>
                                <select title="Status" name="alive" class="form-control classicSelect">
                                    <option value="0" <?php if ($player->alive == 0) echo 'selected = "selected"'; ?>>Non</option>
                                    <option value="1" <?php if ($player->alive == 1) echo 'selected = "selected"'; ?>>Oui</option>
                                </select>
                                <div class="input-group-append input-group-text"><span class="fa fa-notes-medical"></span></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="card">
                    <h5 class="card-header">Position</h5>
                    <div class="card-body">
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Position</strong><input type="hidden" name="oldpos" value=<?= $player->position; ?>></div>
                            <textarea title="Position" id="position" name="position" class="form-control"><?= $player->position ?></textarea>
                            <div class="input-group-append input-group-text"><span class="fa fa-map-marked"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Lieux</strong></div>
                            <select class="form-control classicSelect" onclick="$('#position').val($(this).val())">
                                <option selected value='<?= $player->position ?>'></option>
                                <option value='"[8469.77,9593.17,0.00140381]"'>Le Palais</option>
                                <option value='"[3733.91,12620.6,0.00143886]"'>Sauzon</option>
                                <option value='"[13918,3776.47,0.00143814]"'>Locmaria</option>
                                <option value='"[5379.91,7119.63,0.00144577]"'>Aéroport</option>
                                <option value='"[8117.85,8503.53,0.0938721]"'>Préfecture</option>
                                <option value='"[6731.86,8346.72,0.00145388]"'>Caserne GN LP</option>
                                <option value='"[3440.08,12542.2,0.127217]"'>Caserne GN Sauzon</option>
                                <option value='"[14124.3,3637.06,0.00143814]"'>Caserne GN Locmaria</option>
                                <option value='"[6712.59,8722.29,0.266128]"'>Caserne SP LP</option>
                                <option value='"[2057.62,12947.9,0.00141907]"'>Caserne SP Sauzon</option>
                                <option value='"[13865.8,3528.73,0.00142288]"'>Caserne SP Locmaria</option>
                            </select>
                            <div class="input-group-append input-group-text"><span class="fa fa-map-signs"></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (Auth::isModo()) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <h5 class="card-header">Argent</h5>
                        <div class="card-body">
                            <div class="form-group input-group">
                                <div class="input-group-prepend input-group-text"><strong>Cash</strong></div>
                                <input title="Cash" type="text" name="cash" value="<?= $player->cash; ?>"
                                       class="form-control">
                                <input type="hidden" name="oldcash" value="<?= $player->cash; ?>">
                                <div class="input-group-append input-group-text"><span class="fa fa-euro-sign"></span></div>
                            </div>
                            <?php if ($banq != NULL) { ?>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Livret A</strong></div>
                                    <input title="Livret A" type="text" name="livreta" value="<?= $banq->livreta; ?>"
                                           class="form-control">
                                    <input type="hidden" name="oldlivreta" value="<?= $banq->livreta; ?>">
                                    <div class="input-group-append input-group-text"><span class="fa fa-euro-sign"></span></div>
                                </div>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Livret B</strong></div>
                                    <input title="Livret B" type="text" name="livretb" value="<?= $banq->livretb; ?>"
                                           class="form-control">
                                    <input type="hidden" name="oldlivretb" value="<?= $banq->livretb; ?>">
                                    <div class="input-group-append input-group-text"><span class="fa fa-euro-sign"></span></div>
                                </div>
                                <div class="form-group input-group">
                                    <div class="input-group-prepend input-group-text"><strong>Livret C</strong></div>
                                    <input title="Livret C" type="text" name="livretc" value="<?= $banq->livretc; ?>"
                                           class="form-control">
                                    <input type="hidden" name="oldlivretc" value="<?= $banq->livretc; ?>">
                                    <div class="input-group-append input-group-text"><span class="fa fa-euro-sign"></span></div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <h5 class="card-header">Licences</h5>
                        <div class="card-body">
                            <div class="form-group input-group">
								<textarea title="Licenses" style="height:150px;" name="licenses"
                                          class="form-control"><?= $player->licenses ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <h5 class="card-header">Équipement</h5>
                        <div class="card-body">
                            <div class="form-group input-group">
                        <textarea title="Équipement" <?php if (!Auth::isStaff()) { ?>disabled<?php } ?> style="height:150px;" name="gear" class="form-control"><?= $player->gear
                            ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <h5 class="card-header">Grades</h5>
                        <div class="card-body">
                            <div class="form-group input-group">
                                <div class="input-group-prepend input-group-text"><strong>Gendarme</strong></div>
                                <select id="gradeGendarme" title="Grade Gendarme" name="coplevel" class="form-control">
                                    <option value="0" <?php if ($player->coplevel == 0) echo 'selected = "selected"'; ?>>Non</option>
                                    <option value="1" <?php if ($player->coplevel == 1) echo 'selected = "selected"'; ?>>Élève-Gendarme</option>
                                    <option value="2" <?php if ($player->coplevel == 2) echo 'selected = "selected"'; ?>>Gendarme</option>
                                    <option value="3" <?php if ($player->coplevel == 3) echo 'selected = "selected"'; ?>>Maréchal des logis-chef</option>
                                    <option value="4" <?php if ($player->coplevel == 4) echo 'selected = "selected"'; ?>>Adjudant</option>
                                    <option value="5" <?php if ($player->coplevel == 5) echo 'selected = "selected"'; ?>>Adjudant-Chef</option>
                                    <option value="6" <?php if ($player->coplevel == 6) echo 'selected = "selected"'; ?>>Major</option>
                                    <option value="7" <?php if ($player->coplevel == 7) echo 'selected = "selected"'; ?>>Sous-Lieutenant</option>
                                    <option value="8" <?php if ($player->coplevel == 8) echo 'selected = "selected"'; ?>>Lieutenant</option>
                                    <option value="9" <?php if ($player->coplevel == 9) echo 'selected = "selected"'; ?>>Capitaine</option>
                                    <option value="10" <?php if ($player->coplevel == 10) echo 'selected = "selected"'; ?>>Commandant</option>
                                    <option value="11" <?php if ($player->coplevel == 11) echo 'selected = "selected"'; ?>>Lieutenant-Colonel</option>
                                    <option value="12" <?php if ($player->coplevel == 12) echo 'selected = "selected"'; ?>>Colonel</option>
                                </select>
                            </div>
                            <div class="form-group input-group">
                                <div class="input-group-prepend input-group-text"><strong>Pompier</strong></div>
                                <select id="gradePompier" title="Grade Pompier" name="mediclevel" class="form-control">
                                    <option value="0" <?php if ($player->mediclevel == 0) echo 'selected = "selected"'; ?>>Non</option>
                                    <option value="1" <?php if ($player->mediclevel == 1) echo 'selected = "selected"'; ?>>Sapeur 2nd Classe</option>
                                    <option value="2" <?php if ($player->mediclevel == 2) echo 'selected = "selected"'; ?>>Sapeur 1ère Classe</option>
                                    <option value="3" <?php if ($player->mediclevel == 3) echo 'selected = "selected"'; ?>>Caporal</option>
                                    <option value="4" <?php if ($player->mediclevel == 4) echo 'selected = "selected"'; ?>>Caporal-Chef</option>
                                    <option value="5" <?php if ($player->mediclevel == 5) echo 'selected = "selected"'; ?>>Sergent</option>
                                    <option value="6" <?php if ($player->mediclevel == 6) echo 'selected = "selected"'; ?>>Sergent-Chef</option>
                                    <option value="7" <?php if ($player->mediclevel == 7) echo 'selected = "selected"'; ?>>Adjudant</option>
                                    <option value="8" <?php if ($player->mediclevel == 8) echo 'selected = "selected"'; ?>>Adjudant-Chef</option>
                                    <option value="9" <?php if ($player->mediclevel == 9) echo 'selected = "selected"'; ?>>Lieutenant</option>
                                    <option value="10" <?php if ($player->mediclevel == 10) echo 'selected = "selected"'; ?>>Capitaine</option>
                                    <option value="11" <?php if ($player->mediclevel == 11) echo 'selected = "selected"'; ?>>Commandant</option>
                                    <option value="12" <?php if ($player->mediclevel == 12) echo 'selected = "selected"'; ?>>Colonel</option>
                                </select>
                            </div>
                            <div class="form-group input-group">
                                <div class="input-group-prepend input-group-text"><strong>Service Public</strong></div>
                                <select id="gradeSP" title="Grade Service Public" name="publique"
                                        class="form-control">
                                    <option value="0" <?php if ($player->publique == 0) echo 'selected = "selected"'; ?>>Non</option>
                                    <option value="1" <?php if ($player->publique == 1) echo 'selected = "selected"'; ?>>Grade 1 - 1000€</option>
                                    <option value="2" <?php if ($player->publique == 2) echo 'selected = "selected"'; ?>>Grade 2 - 1300€</option>
                                    <option value="3" <?php if ($player->publique == 3) echo 'selected = "selected"'; ?>>Grade 3 - 1400€</option>
                                    <option value="4" <?php if ($player->publique == 4) echo 'selected = "selected"'; ?>>Grade 4 - 1700€</option>
                                    <option value="5" <?php if ($player->publique == 5) echo 'selected = "selected"'; ?>>Grade 5 - 1800€</option>
                                    <option value="6" <?php if ($player->publique == 6) echo 'selected = "selected"'; ?>>Grade 6 - 2000€</option>
                                    <option value="7" <?php if ($player->publique == 7) echo 'selected = "selected"'; ?>>Grade 7 - 2200€</option>
                                    <option value="8" <?php if ($player->publique == 8) echo 'selected = "selected"'; ?>>Grade 8 - 2400€</option>
                                </select>
                            </div>
                            <div class="form-group input-group">
                                <div class="input-group-prepend input-group-text"><strong>A. Pénitentiaire</strong></div>
                                <select id="gradePrison" title="Grade AP" name="penit" class="form-control">
                                    <option value="0" <?php if ($player->penit == 0) echo 'selected = "selected"'; ?>>Non</option>
                                    <option value="1" <?php if ($player->penit == 1) echo 'selected = "selected"'; ?>>Surveillant Stagiaire</option>
                                    <option value="2" <?php if ($player->penit == 2) echo 'selected = "selected"'; ?>>Surveillant Titulaire</option>
                                    <option value="3" <?php if ($player->penit == 3) echo 'selected = "selected"'; ?>>Surveillant Principal</option>
                                    <option value="4" <?php if ($player->penit == 4) echo 'selected = "selected"'; ?>>Surveillant Brigadier</option>
                                    <option value="5" <?php if ($player->penit == 5) echo 'selected = "selected"'; ?>>Premier Surveillant</option>
                                    <option value="6" <?php if ($player->penit == 6) echo 'selected = "selected"'; ?>>Major de Prison</option>
                                    <option value="7" <?php if ($player->penit == 7) echo 'selected = "selected"'; ?>>Lieutenant de Prison</option>
                                    <option value="8" <?php if ($player->penit == 8) echo 'selected = "selected"'; ?>>Capitaine de Prison</option>
                                    <option value="9" <?php if ($player->penit == 9) echo 'selected = "selected"'; ?>>Commandant de Prison</option>
                                    <option value="10" <?php if ($player->penit == 10) echo 'selected = "selected"'; ?>>Directeur de Prison</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <h5 class="card-header">Informations RP</h5>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><strong>Malade :</strong> <?php if ($player->medical == "[[],[],[]]") echo ' Non'; else echo " Oui" ?></li>
                                <li><strong>Permis :</strong> <?php if ($player->permis == 0) echo 'Non'; else echo "Oui - ".$player->permis ?> Points</li>
                                <li><strong>Suspension de Permis :</strong> <?php if ($player->permis_time == 0) echo 'Aucune'; else echo $player->permis_time." Jour(s)" ?> </li>
                                <li><strong>Type de contrat Axa :</strong> <?php if ($player->axa == 4) echo 'Contrat Premium (Donateur)'; else echo "Type ".$player->axa ?></li>
                                <li><strong>Nbr. assurance perso. :</strong> <?php if ($player->axacount == 0) echo 'Aucune'; else echo $player->axacount." assurance(s)" ?></li>
                                <li><strong>Nbr. assurance pro. :</strong> <?php if ($player->axaprocount == 0) echo 'Aucune'; else echo $player->axaprocount." assurance(s)" ?>
                                </li>
                                <li><strong>Emprisonné(e)
                                            :</strong> <?php if ($player->jail == 0) echo 'Non'; else echo "Oui - ".number_format($player->jailtime / 60, 0, ",", ".")." heure(s) - Cellule n°".$player->jailcoffre ?>
                                </li>
                                <li><strong>Bracelet électronique
                                            :</strong> <?php if ($player->bracelet == 0) echo 'Non'; else echo "Oui - ".number_format($player->bracelettime / 60, 0, ",", ".")." heure(s)" ?>
                                </li>
                                <li><strong>Numéro de téléphone :</strong> <?php if ($phone == NULL) echo 'Aucun'; else echo $phone->number; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </form>
    <div class="row mt-2">
        <div class="col">
            <?php if (Auth::isModo()) { ?>
                <div class="card">
                    <h5 class="card-header" onclick="show('vehicules')">
                        Véhicules <span class="badge badge-dark"><?= $vehicules->rowCount() ?></span>
                        <?php if (Auth::isAdmin()) { ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm float-md-right" data-toggle="modal" data-target="#addVehiculeModal">
                                <span class="fa fa-plus"></span> Ajouter
                            </button>
                        <?php } ?>
                    </h5>
                    <div class="card-body" id="vehicules">
                        <table class="table card-text table-bordered table-hover table-responsive-sm">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Modèle</th>
                                <th>Assurance</th>
                                <th>Essence</th>
                                <th>Inventaire</th>
                                <th>Sinistres</th>
                                <th>Plaque</th>
                                <th>Date d'achat</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php while ($vehicule = $vehicules->fetch(PDO::FETCH_OBJ)) { ?>
                                <tr <?php if ($vehicule->active) echo 'class="table-success"'; ?>>
                                    <td style="text-align:center" title="ID: <?= $vehicule->id ?> - Garage n°<?= $vehicule->id_garage ?>" data-toggle="tooltip"><?php
                                        if ($vehicule->type == "Car") echo "<span class='fa fa-car'></span>";
                                        else if ($vehicule->type == "Ship") echo "<span class='fa fa-ship'></span>";
                                        else if ($vehicule->type == "Air") echo "<span class='fa fa-helicopter'></span>";
                                        else echo "<span class='fa fa-warehouse'></span>"; ?></td>
                                    <td><?= str_replace(['V_ALF_', 'ALF_', '_'], ['', '', ' '], $vehicule->classname) ?>
                                        <?php
                                        if ($vehicule->active) echo "<span class='badge badge-success float-right ml-1'>Sorti</span>";
                                        if ($vehicule->isInHome and $vehicule->active) echo "<span class='badge badge-primary float-right ml-1'><span class='fa fa-home'></span></span>";
                                        if ($vehicule->peinture != '[]' and $vehicule->peinture != '"[]"') echo "<span class='badge badge-secondary float-right ml-1' data-toggle='tooltip' title='".str_replace([
                                                '"',
                                                '[', ',', ']', '`',
                                            ], ['', '', ', ', '', ''], $vehicule->peinture)."'><span class='fa fa-paint-brush'></span></span>";
                                        if (intval(str_replace(['"', ',', '[', ']', '.'], '', $vehicule->damage)) != 0) echo "<span class='badge badge-warning float-right ml-1' data-toggle='tooltip' title='Endommagé'><span class='fa fa-car-crash'></span></span>";
                                        if ($vehicule->lockveh == 1) echo "<span class='badge badge-danger float-right' data-toggle='tooltip' title='Fourrière - Restant : ".$vehicule->locktime." jours'><span class='fa fa-car'></span></span>";
                                        ?><br>(<?= $vehicule->classname ?>)
                                    </td>
                                    <td><?php if ($vehicule->insure == 1) echo "<span class='fa fa-check'></span>";
                                        else if ($vehicule->insure == 2) echo "<span class='badge badge-info'>Pro</span>";
                                        else if ($vehicule->insure == 3) echo "<span class='badge badge-info'>Entreprise</span>"; ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= $vehicule->fuel * 100 ?>%;"></div>
                                        </div>
                                    </td>
                                    <td><?= str_replace(['[', ']', '"', '`', ',', ', ,'], ['', '', '', '', ', ', ''], $vehicule->gear) ?></td>
                                    <td><?php if ($vehicule->insurecount > 0) echo $vehicule->insurecount ?></td>
                                    <td><?= str_replace(['``', '`', '[', ']', ',', '"'], [' ', '', '', '', '', ''], $vehicule->plate) ?></td>
                                    <td><?= $vehicule->insert_time ?></td>
                                    <td><?php if (Auth::isStaff()) { ?>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($vehicule->active == 0) { ?>
                                                    <a onclick="return confirm('Supprimer le véhicule :');" href="/Admin/deleteVehicule?id=<?= $vehicule->id ?>"
                                                       class="btn btn-sm btn-danger">
                                                        <span class="fa fa-trash"></span></a>
                                                <?php } else { ?>
                                                    <a href="/joueur.php?id=<?= $player->playerid ?>&vehicule=<?= $vehicule->id ?>&sendHome" class="btn btn-primary"
                                                       onclick="return confirm('Renvoyer le véhicule au garage (prochain reboot) : ');">
                                                        <span class="fa fa-home"></span></a>
                                                <?php } ?>
                                                <button class="btn btn-info"
                                                        onclick='editVehicule(<?= $vehicule->id ?>, "<?= $vehicule->type ?>", "<?= $vehicule->classname ?>",
                                                                "<?= str_replace(['``', '`', '[', ']', ',', '"',], [' ', '', '', '', '', ''], $vehicule->plate) ?>",
                                                                (String.raw`<?= str_replace(['"', '`'], ['', '|'], $vehicule->peinture) ?>`).replace(/[|]/gi, "`").replace(/[\\]/gi,
                                                                "\\\\"), <?= $vehicule->fuel ?>,
                                                        <?= $vehicule->insure ?>,<?= $vehicule->insurecount ?>)'>
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <h5 class="card-header" onclick="show('maisons')">Maisons <span class="badge badge-dark"><?= $houses->rowCount() ?></span></h5>
                    <div class="card-body" id="maisons">
                        <table class="table card-text table-bordered table-hover table-responsive-md">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Modèle</th>
                                <th>Position</th>
                                <th>Date d'achat</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php while ($house = $houses->fetch(PDO::FETCH_OBJ)) { ?>
                                <tr>
                                    <td style="text-align:center" title="ID: <?= $house->id ?>" data-toggle="tooltip"><span class='fas fa-home'></span></td>
                                    <td><?= str_replace(['Land', 'ALF_', '_'], ['', '', ' '], $house->classname) ?><br>(<?= $house->classname ?>)</td>
                                    <td>
                                        <?php $d = explode(",", explode('[', $house->pos)[1]);
                                        $xArma = pad(floor(intval($d[0]) / 100));
                                        $yArma = pad(144 + floor((intval($d[1]) - 64) / 100)); ?>
                                        <?= $xArma ?>.<?= $yArma ?><br>
                                        <?= $house->pos ?>
                                    </td>
                                    <td><?= $house->insert_time ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/joueur.php?id=<?= $player->playerid ?>&maison=<?= $house->id ?>&deleteMaison&pos=<?= $house->pos ?>"
                                               onclick="return confirm('Supprimer la maison (<?= $house->pos ?>');"
                                               class="btn btn-danger"><span class="fa fa-trash"></span></a>

                                            <a href="/house.php?id=<?= $house->id ?>" class="btn btn-info"><span class="fa fa-eye"></span></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <?php if (isset($service)) { ?>
                <div class="card">
                    <h5 class="card-header" onclick="show('service')">Service <span class="badge badge-dark"><?= $service->rowCount() ?></span></h5>
                    <div class="card-body" id="service">
                        <table class="table card-text table-bordered table-hover table-responsive-md">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Contenu</th>
                                <th>Durée</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $heures = 0;
                            $precedent = ".";
                            $dateDebut = "";
                            while ($entry = $service->fetch(PDO::FETCH_OBJ)) { ?>
                                <tr <?php if (strtolower($entry->text[0]) == "p") echo 'class="table-success"';
                                else if (strtolower($entry->text[0]) == "t") echo 'class="table-warning"'; ?>>
                                    <td><?= $entry->insert_time ?></td>
                                    <td><?= $entry->text ?></td>

                                    <?php if (strtolower($precedent[0]) == "p" and strtolower($entry->text[0]) == "t") {
                                        try {
                                            $date = (new DateTime($entry->insert_time))->diff(new DateTime($dateDebut));
                                            $dH = $date->days * 24 + $date->h + $date->i / 60;
                                            $heures += $dH;
                                            echo '<td>'.floor($dH).' h '.round(($dH - floor($dH)) * 60, 0).' mn</td>';
                                        } catch (Exception $e) {
                                        }
                                    }
                                    else echo '<td></td>';
                                    $precedent = $entry->text;
                                    $dateDebut = $entry->insert_time; ?>
                                </tr>
                            <?php } ?>
                            <tr class="table-info">
                                <td colspan="2" class="text-center"><b>Temps de service <em class="small">(31 derniers jours)</em></b></td>
                                <td><b><em><?= floor($heures) ?></em> h
                                        <em><?= round(($heures - floor($heures)) * 60, 0) ?></em> mn</b>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <div class="card">
                <h5 class="card-header" onclick="show('logs')">Logs Serveur</h5>
                <div class="card-body" id="logs">
                    <table class="table card-text table-bordered table-hover table-responsive-md">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Texte</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($log = $logs->fetch(PDO::FETCH_OBJ)) { ?>
                            <tr <?php
                            if ($log->type == "MORT") echo "class='table-danger'";
                            else if ($log->type == "BANK") echo "class='table-primary'";
                            else if ($log->type == "GIVE") echo "class='table-success'";
                            else if (in_array($log->type, ["LEBONCOIN", "VENTE", "VEHICULE", "MAISON"])) echo "class='table-info'";
                            else if ($log->type == "KNOCKOUT") echo "class='table-warning'";
                            ?>>
                                <td><?= $log->type ?></td>
                                <td><?= $log->text ?></td>
                                <td><?= $log->insert_time ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (Auth::isAdmin()) { ?>
                <div class="card">
                    <h5 class="card-header" onclick="show('logs_panel')">Logs Panel</h5>
                    <div class="card-body" id="logs_panel">
                        <table class="table card-text table-bordered table-hover table-responsive-md">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Date</th>
                                <th>Texte</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($logPanel = $logsPanel->fetch(PDO::FETCH_OBJ)) { ?>
                                <tr>
                                    <td><?= $logPanel->username ?></td>
                                    <td><?= $logPanel->datetime ?></td>
                                    <td><?= $logPanel->texte ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php if (Auth::isModo()) { ?>
    <?php if (Auth::isAdmin()) { ?>
        <div class="modal fade" id="addVehiculeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form method="post" action="joueur?id=<?= $player->playerid ?>&addVehicule">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Ajout de véhicule</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="classname">ClassName</label>
                                <div class="input-group">
                                    <input type="text" title="ClassName de véhicule valable (liste sur la page Dispo Véhicules)" name="classname" id="classname"
                                           required class="form-control"/>
                                    <div class="input-group-append">
                                        <a href="/Admin/Vehicules.php" target="_blank" class="btn btn-info"><span class="fa fa-list"></span></a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select name="type" id="type" required class="form-control">
                                    <option value="Car">Car</option>
                                    <option value="Air">Air</option>
                                    <option value="Ship">Ship</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="plaque">Plaque</label>
                                <input type="text" pattern="[A-Z]{2}-[0-9]{3}-[A-Z]{2}" name="plaque" id="plaque" title="Format de plaque valide (ex: AB-123-CD)"
                                       class="form-control"
                                       placeholder="AB-123-CD"/>
                            </div>
                            <div class="form-group">
                                <label for="message">Raison</label>
                                <input type="text" name="message" id="message" required class="form-control"/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>
    <div class="modal fade" id="editVehiculeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" id="editVehiculeForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modification de véhicule</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php if (Auth::isAdmin()) { ?>
                            <div class="form-group">
                                <label for="pid">PID</label>
                                <input type="text" name="pid" id="pid" required value="<?= $player->playerid ?>" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="classnameEdit">ClassName</label>
                                <div class="input-group">
                                    <input type="text" title="ClassName de véhicule valable (liste sur la page Dispo Véhicules)" name="classnameEdit" id="classnameEdit"
                                           required class="form-control"/>
                                    <div class="input-group-append">
                                        <a href="/Admin/Vehicules.php" target="_blank" class="btn btn-info"><span class="fa fa-list"></span></a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="typeEdit">Type</label>
                                <select name="typeEdit" id="typeEdit" required class="form-control">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="peinture">Peinture</label>
                                <input type="text" name="peinture" id="peinture" class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="plaque">Plaque</label>
                                <input type="text" name="plate" id="plate" class="form-control"
                                       pattern="[A-Z]{2}-[0-9]{3}-[A-Z]{2}" title="Format de plaque valide (ex: AB-123-CD)"/>

                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="fuel">Essence</label>
                            <input type="text" name="fuel" id="fuel" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="insure">Assurance</label>
                            <select name="insure" id="insure" required class="form-control">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                                <option value="2">Pro</option>
                                <option value="3">Entreprise</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="insurecount">Sinistres</label>
                            <input type="number" name="insurecount" id="insurecount" class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="message">Raison</label>
                            <input type="text" name="message" id="message" required class="form-control"/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
    <script src="https://armstalker.com/guid/add/core.js"></script>
    <script src="https://armstalker.com/guid/add/md5.js"></script>
    <script src="https://armstalker.com/guid/add/lib-typedarrays.js"></script>
    <script src="https://armstalker.com/guid/add/BigInteger.min.js"></script>
    <!--suppress JSUnresolvedVariable -->
    <script>
        function uid2guid(uid) {
            if (!uid) return;

            let steamId = bigInt(uid), parts = [0x42, 0x45, 0, 0, 0, 0, 0, 0, 0, 0];
            for (let i = 2; i < 10; i++) {
                let res = steamId.divmod(256);
                steamId = res.quotient;
                parts[i] = res.remainder.toJSNumber();
            }
            let wordArray = CryptoJS.lib.WordArray.create(new Uint8Array(parts));
            return CryptoJS.MD5(wordArray).toString();
        }

        function show(selector) {
            if ($('#' + selector).is(':visible')) selector = "";

            switch (selector) {
                case 'logs':
                    $('#logs').show();
                    $('#logs_panel').hide();
                    $('#vehicules').hide();
                    $('#maisons').hide();
                    $('#service').hide();
                    break;
                case 'vehicules':
                    $('#logs').hide();
                    $('#logs_panel').hide();
                    $('#vehicules').show();
                    $('#maisons').hide();
                    $('#service').hide();
                    break;
                case 'maisons':
                    $('#logs').hide();
                    $('#logs_panel').hide();
                    $('#vehicules').hide();
                    $('#maisons').show();
                    $('#service').hide();
                    break;
                case 'service':
                    $('#logs').hide();
                    $('#logs_panel').hide();
                    $('#vehicules').hide();
                    $('#maisons').hide();
                    $('#service').show();
                    break;
                case 'logs_panel':
                    $('#logs').hide();
                    $('#logs_panel').show();
                    $('#vehicules').hide();
                    $('#maisons').hide();
                    $('#service').hide();
                    break;
                default:
                    $('#logs').hide();
                    $('#logs_panel').hide();
                    $('#vehicules').hide();
                    $('#maisons').hide();
                    $('#service').hide();
                    break;
            }
        }

        function editVehicule(id, type, classname, plaque, peinture, fuel, insure, insurecount) {
            $('#editVehiculeForm').attr('action', "joueur?id=<?= $player->playerid ?>&editVehicule=" + id);
            let types = ['Car', 'Air', 'Ship', type];

            $("#typeEdit").empty();
            $.each(types, function (key, t) {
                $("#typeEdit").append($('<option>', {value: t, text: t})).val(type);
            });
            $('#classnameEdit').val(classname);
            $('#plate').val(plaque);
            $('#peinture').val(peinture);
            $('#fuel').val(fuel);
            $('#insure').val(insure);
            $('#insurecount').val(insurecount);
            $('#editVehiculeModal').modal();
        }

        show();
        $('#GUID').html(uid2guid('<?= $player->playerid ?>'));
    </script>
<?php require_once('templates/bottom.html');
$resPlayer->closeCursor();
$resBank->closeCursor();
$vehicules->closeCursor();
$houses->closeCursor();
$logs->closeCursor();
if (isset($service)) $service->closeCursor();
$logsPanel->closeCursor();