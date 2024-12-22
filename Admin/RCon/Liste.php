<?php

use Nizarii\ARC;

require_once '../../init.php';
require_once 'ARC.php';

if (!Auth::hasRCon()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès au RCon."];
    header('Location: /');
    exit();
}
$paneluser = $_SESSION['Auth']['username'];
try {
    $rcon = new ARC($ipRCon, $passwdRCon, $portRCon);
    if (isset($_GET) and isset($_POST) and isset($_GET['kick']) and isset($_GET['name']) and isset($_POST['reasonKick'])) {
        $id = $_GET['kick'];
        $name = $_GET['name'];
        $reason = $_POST['reasonKick'];
        $rcon->kickPlayer($id, $reason);
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Kick ($name - $reason).')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => $_GET['name']." a été kické."];
        header('Location: /Admin/RCon/Liste.php');
        exit();
    }
    else if (isset($_GET) and isset($_POST) and isset($_GET['ban']) and isset($_GET['name']) and isset($_POST['reasonBan']) and isset($_POST['time'])) {
        $guid = $_GET['ban'];
        $name = $_GET['name'];
        $time = $_POST['time'];
        $message = $time == '0' ? "perm" : $time." jours";
        $reason = $name.' - '.$_POST['reasonBan'].' - '.$message.' - '.$paneluser.' - '.date('d/m/y H:i');

        $rcon->banPlayer($guid, $reason, $time * 24 * 60);
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Ban ($reason).')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => $_GET['name']." a été banni."];
        header('Location: /Admin/RCon/Liste.php');
        exit();
    }

    $players = $rcon->getPlayersArray();
} catch (Exception $e) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => "Erreur (".$e->getMessage().")."];
    header('Location: /');
    exit();
}

require_once '../../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">RCon - <span class="badge badge-dark"><?= count($players) ?> joueurs</span></h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <div class="btn-group">
                <a href="/Admin/RCon/Bans.php" class="btn btn-info">
                    <i class="fa fa-list"></i> Liste des Bans
                </a>
                <a href="/Admin/RCon/AddBan" class="btn btn-primary">
                    <i class="fa fa-ban"></i> Ban GUID
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-responsive-sm table-hover table-bordered bg-white mt-2">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>IP</th>
                    <th>Ping</th>
                    <th>GUID</th>
                    <th>Nom</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($players); $i++) { ?>
                    <tr>
                        <td><?= $players[$i][0] ?></td>
                        <td><?= $players[$i][1] ?></td>
                        <td><?= $players[$i][2] ?></td>
                        <td><?= $players[$i][3] ?></td>
                        <td><?= $players[$i][4] ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-warning btn-sm" onclick="kickPlayer(<?= $players[$i][0] ?>, '<?= $players[$i][4] ?>')">
                                    <i class="fa fa-eject"></i> Kick
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="banPlayer(<?= $players[$i][0] ?>, '<?= $players[$i][4] ?>')">
                                    <i class="fa fa-ban"></i> Ban
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="kickModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" onsubmit="return confirm('Confirmer le kick : ');">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kick - <span></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="form-group">
                            <label for="reasonKick">Raison</label>
                            <input type="text" title="Raison du kick" name="reasonKick" id="reasonKick" required class="form-control"/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="banModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" onsubmit="return confirm('Confirmer le ban : ');">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kick - <span></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reasonBan">Raison</label>
                            <input type="text" title="Raison du ban" name="reasonBan" id="reasonBan" required class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label for="time">Durée en jours <em>(Mettre 0 pour un ban permanent)</em></label>
                            <input type="number" step="1" min="0" title="Durée du ban" name="time" id="time" required class="form-control"  /
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        function kickPlayer(id, name) {
            let url = "/Admin/RCon/Liste.php?kick=" + id + "&name=" + name;

            $('#kickModal .modal-title span').html(name);
            $('#kickModal form').attr('action', url);
            $('#kickModal').modal();
        }

        function banPlayer(id, name) {
            let url = "/Admin/RCon/Liste.php?ban=" + id + "&name=" + name;

            $('#banModal .modal-title span').html(name);
            $('#banModal form').attr('action', url);
            $('#banModal').modal();
        }
    </script>
<?php
require_once '../../templates/bottom.html';
