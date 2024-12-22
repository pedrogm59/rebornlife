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
        'timeoutSec' => 5,
        'debug'      => FALSE,
    ]);

    if (isset($_GET) and isset($_POST) and isset($_GET['unban']) and isset($_GET['reason']) and isset($_GET['guid']) and isset($_POST['reasonUnban'])) {
        $id = intval($_GET['unban']);
        $reason = $_GET['reason'];
        $guid = $_GET['guid'];
        $reasonUnban = $_POST['reasonUnban'];
        $paneluser = $_SESSION['Auth']['username'];

        $rcon->removeBan($id);
        $_SESSION['alert'] = ['type' => 'success', 'message' => "Ban supprimé."];
        $message = addslashes("Unban (".$guid." - ".$reason.") (".$reasonUnban.")");
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','$message.')");
        header('Location: /Admin/RCon/Bans.php');
        exit();
    }

    $bans = array_reverse($rcon->getBansArray());
} catch (Exception $e) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => "Erreur (".$e->getMessage().")."];
    header('Location: /');
    exit();
}

require_once '../../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Bans - <span class="badge badge-dark"><?= count($bans) ?></span></h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <div class="btn-group">
                <a href="/Admin/RCon/Liste.php" class="btn btn-primary">
                    <i class="fa fa-users"></i> Joueurs
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="input-group mt-2">
                <input id="inputSearchBans" onkeyup="searchBans();" class="form-control" type="search" placeholder="Rechercher par GUID ou raison">
                <div class="input-group-append">
                    <div class="input-group-text" onclick="$('#inputSearchBans').val('').keyup();"><i class="fas fa-search"></i></div>
                </div>
            </div>
            <table class="table table-responsive-sm table-hover table-sm table-bordered bg-white mt-2" id="tableBans">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>GUID</th>
                    <th>Durée</th>
                    <th>Raison</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($bans); $i++) { ?>
                    <tr <?php if ($bans[$i][2] != "perm") echo " class='table-warning'" ?>>
                        <td><?= $bans[$i][0] ?></td>
                        <td><?= $bans[$i][1] ?></td>
                        <td><?= $bans[$i][2] ?></td>
                        <td><?= $bans[$i][3] ?></td>
                        <td>
                            <button onclick="unbanPlayer(<?= $bans[$i][0] ?>, '<?= $bans[$i][3] ?>', '<?= $bans[$i][1] ?>')" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="unbanModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" onsubmit="return confirm('Confirmer le débannissement : ');">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Débannissement - <span></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reasonUnban">Raison</label>
                            <input type="text" title="Raison de l'unban" name="reasonUnban" id="reasonUnban" required class="form-control"/>
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
        function unbanPlayer(id, reason, guid) {
            let url = "/Admin/RCon/Bans.php?unban=" + id + "&reason=" + reason + "&guid=" + guid;

            $('#unbanModal .modal-title span').html(reason);
            $('#unbanModal form').attr('action', url);
            $('#unbanModal').modal();
        }

        function searchBans() {
            let filter = document.getElementById("inputSearchBans").value.toUpperCase(),
                tr = document.getElementById("tableBans").getElementsByTagName("tr");

            for (let i = 0; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td");
                if (td[0]) {
                    if (td[1].innerHTML.toUpperCase().indexOf(filter) > -1 || td[3].innerHTML.toUpperCase().indexOf(filter) > -1)
                        tr[i].style.display = "";
                    else tr[i].style.display = "none";
                }
            }
        }
    </script>
<?php
require_once '../../templates/bottom.html';
