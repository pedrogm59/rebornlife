<?php
include '../../init.php';
if (!Auth::isAdmin()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès aux logs du Panel."];
    header('Location: /');
    exit();
}
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";
$search = $DB->prepare("SELECT DISTINCT * FROM logs_panel WHERE username LIKE :search_value OR texte LIKE :search_value ORDER BY datetime DESC LIMIT 0, 500");
$search->bindValue(':search_value', '%'.$search_value.'%');
$search->execute();
include '../../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Logs</h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <form method="get" id="searchLogs">
                <div class="input-group mb-2">
                    <input name="q" id="q" title="Recherche" placeholder="Recherche" class="form-control" autocomplete="off" type="search" value="<?= $search_value ?>"/>
                    <div class="input-group-append input-group-text" onclick="$('#q').val(''); $('#searchLogs').submit();">
                        <span class="fa fa-search"></span>
                    </div>
                </div>
            </form>
            <table class="table table-responsive-sm table-bordered bg-white">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Date</th>
                    <th>Texte</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr>
                        <td<?php if ($row->username == "Lya" or $row->username == "Kilian"): ?> style="color: deeppink;"<?php endif ?>><?= $row->username ?></td>
                        <td><?= $row->datetime ?></td>
                        <td><?= $row->texte ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
<?php
$search->closeCursor();
include('../../templates/bottom.html');