<?php
include '../init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès à la recherche de véhicule."];
    header('Location: /');
    exit();
}

if (isset($_GET) and (isset($_GET['search_plaque']) or isset($_GET['search_classname']))) {
    $search_plaque = $_GET['search_plaque'];
    $search_classname = $_GET['search_classname'];
    $plaque = '%';
    for ($i = 0; $i < strlen($search_plaque); $i++)
        $plaque .= '`'.$search_plaque[$i].($i == strlen($search_plaque) - 1 ? '' : '`,');
    $plaque .= '%';
    $classname = "%".str_replace(' ', '_', $search_classname)."%";
    $search_plaques = $DB->query("SELECT pid, classname, players.name, plate FROM vehicles JOIN players ON players.playerid = vehicles.pid
        WHERE plate LIKE '$plaque' and classname LIKE '$classname'") or die(print_r($DB->errorInfo()));
}
else {
    $search_plaque = "";
    $search_classname = "";
}

include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Recherche - Véhicules</h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <form method="get" class="form form-inline">
                <div class="input-group mr-1">
                    <input id="search_plaque" name="search_plaque" class="form-control" placeholder="Plaque" value="<?= $search_plaque ?>">
                </div>
                <div class="input-group mr-1">
                    <input id="search_classname" name="search_classname" class="form-control" placeholder="Classname" value="<?= $search_classname ?>">
                </div>
                <button class="btn btn-primary" type="submit">
                    <i class="fa fa-search"></i>
                </button>
            </form>
            <table class="table table-responsive-sm table-bordered bg-white mt-2" id="tableVehicule">
                <thead>
                <tr>
                    <th>Propriétaire</th>
                    <th>Modèle</th>
                    <th>Plaque</th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($search_plaques)) {
                    while ($row = $search_plaques->fetch(PDO::FETCH_OBJ)) { ?>
                        <tr>
                            <td><a href="/joueur.php?id=<?= $row->pid ?>"><?= $row->name ?></a></td>
                            <td><?= str_replace(['V_ALF_', 'ALF_', '_'], ['', '', ' '], $row->classname) ?></td>
                            <td><?= str_replace(['``', '`', '[', ']', ',', '"'], [' ', '', '', '', '', ''], $row->plate) ?></td>
                        </tr>
                    <?php }
                }
                else { ?>
                    <tr>
                        <td colspan="3"><em>Pas de véhicule répondant à cette recherche.</em></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
if (isset($search_plaques)) $search_plaques->closeCursor();
include('../templates/bottom.html');