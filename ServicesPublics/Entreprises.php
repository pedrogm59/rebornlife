<?php
include '../init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = array('type' => 'warning', 'message' => "Vous n'avez pas accès à la liste des entreprises.");
    header('Location: /');
    exit();
}
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";
$search = $DB->query("SELECT business.id, business.secteur, business.name, business.active, business.del, business.siret, players.name as ownername, players.playerid as ownerid 
FROM business LEFT JOIN players ON business.owner = players.playerid");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Entreprises</h2>
        </div>
    </header>
    <table class="table table-responsive-sm table-bordered bg-white table-hover">
        <thead>
        <tr>
            <th>SIRET</th>
            <th>Nom</th>
            <th>Propriétaire</th>
            <th>Secteur</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
            <tr onclick="window.href.location='/ServicesPublics/Entreprise.php?id=<?= $row->id ?>';"
                <?php if ($row->del == 1) echo 'class="table-secondary"'; else if ($row->active == 0) echo 'class="table-warning"'; ?>>
                <td><?= $row->siret ?></td>
                <td><?php if (Auth::isStaff()) { ?><a href="/ServicesPublics/Entreprise.php?id=<?= $row->id ?>"><?php } ?>
                        <?= str_replace(array('``', '`', '[', ']', ',', '"'), array(' ', '', '', '', '', ''), $row->name) ?></a></td>
                <td><a href="/joueur.php?id=<?= $row->ownerid ?>"><?= $row->ownername ?></a></td>
                <td><?= $row->secteur ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php
$search->closeCursor();
include('../templates/bottom.html');