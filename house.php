<?php
require_once('init.php');
$resHouse = $DB->prepare('SELECT * FROM houses WHERE id = :id') or die(print_r($DB->errorInfo()));
$resHouse->bindParam(':id', $_GET['id']);
$resHouse->execute();
$house = $resHouse->fetch(PDO::FETCH_OBJ);

$resPlayer = $DB->prepare('SELECT playerid, name FROM players WHERE playerid = :playerid') or die(print_r($DB->errorInfo()));
$resPlayer->bindParam(':playerid', $house->pid);
$resPlayer->execute();
$player = $resPlayer->fetch(PDO::FETCH_OBJ);

if ($house == NULL or empty($house)) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Ce bien n'existe pas."];
    header('Location: /');
    exit();
}

if (isset($_GET) and isset($_GET['mobilier']) and isset($_GET['classname']) and isset($_GET['pos']) and isset($_GET['deleteMobilier']) and Auth::isModo()) //Delete MOBILIER
{
    $idMobilier = $_GET['mobilier'];
    $paneluser = $_SESSION['Auth']['username'];
    $houseid = $_GET['id'];
    $playerid = $_GET['pid'];
    $pos = $_GET['pos'];
    $classname = $_GET['classname'];
    $name = $player->name;
    $DB->exec("DELETE FROM mobiliers WHERE id=$idMobilier;");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Mobilier ($classname |$pos) de $name supprimée.')");
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Mobilier supprimée.'];
    header('Location: /house.php?id='.$houseid);
    exit();
}

//$mobiliers = $DB->prepare('SELECT * FROM mobiliers WHERE pid = :pid AND id_house = :id') or die(print_r($DB->errorInfo()));
$mobiliers = $DB->prepare('SELECT * FROM mobiliers WHERE pid = :pid') or die(print_r($DB->errorInfo()));
$mobiliers->bindParam(':pid', $house->pid);
//$mobiliers->bindParam(':id', $house->id);
$mobiliers->execute();

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
            <h2 class="text-center display-4"><?= $house->classname ?> - <?= $house->id ?></h2>
        </div>
    </header>
    <form method="post" autocomplete="off">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Informations</h5>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php $d = explode(",", explode('[', $house->pos)[1]);
                            $xArma = pad(floor(intval($d[0]) / 100));
                            $yArma = pad(144 + floor((intval($d[1]) - 64) / 100)); ?>

                            <li><strong>ID :</strong> <?= $house->id ?></li>
                            <li><strong>Propriétaire :</strong> <a href="/joueur.php?id=<?= $house->pid ?>"><?= $player->name ?></a></li>
                            <li><strong>Steam ID 64 Propriétaire :</strong> <?= $house->pid ?></li>
                            <li><strong>Date d'achat :</strong> <?= $house->insert_time ?></li>
                            <li><strong>Position :</strong> <?= $xArma ?>.<?= $yArma ?> - <?= $house->pos ?></li>
                            <li><strong>Sonnette :</strong> <?php if ($house->sonnette == 0) echo " Non"; else echo " Oui" ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8 p-1" id="mapDiv">
            </div>
        </div>
    </form>
<?php if (Auth::isModo()): ?>
    <div class="row mt-2">
        <div class="col">
            <div class="card">
                <h5 class="card-header" onclick="show('mobiliers')">Mobiliers <span class="badge badge-dark"><?= $mobiliers->rowCount() ?></span></h5>
                <div class="card-body" id="mobiliers">
                    <table class="table card-text table-bordered table-hover table-responsive-md">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Modèle</th>
                            <th>Position</th>
                            <th>Inventaire</th>
                            <th>Date de placement</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($mobilier = $mobiliers->fetch(PDO::FETCH_OBJ)): ?>
                            <tr>
                                <td style="text-align:center" title="ID: <?= $mobilier->id ?>" data-toggle="tooltip"><span class='fas fa-home'></span></td>
                                <td><?= str_replace(['Land', 'ALF_', '_'], ['', '', ' '], $mobilier->classname) ?><br>(<?= $mobilier->classname ?>)</td>
                                <td><?= $mobilier->pos ?></td>
                                <td><?= getGear(str_replace(['"', '`', '[', ']', ','], ['', '', '', '', ' '], $mobilier->gear)) ?></td>
                                <td><?= $mobilier->insert_time ?></td>
                                <td>
                                    <a href="/house?pid=<?= $player->playerid ?>&id=<?= $house->id ?>&mobilier=<?= $mobilier->id ?>&deleteMobilier&classname=<?= $mobilier->classname ?>&pos=<?= $mobilier->pos ?>"
                                       onclick="return confirm('Supprimer le mobilier (<?= $mobilier->classname ?>)');"
                                       class="btn btn-danger"><span class="fa fa-trash"></span></a>
                                </td>
                            </tr>
                        <?php endwhile ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
    <script src="https://armstalker.com/guid/add/core.js"></script>
    <script src="https://armstalker.com/guid/add/md5.js"></script>
    <script src="https://armstalker.com/guid/add/lib-typedarrays.js"></script>
    <script src="https://armstalker.com/guid/add/BigInteger.min.js"></script>
    <script>
        function show(selector) {
            if ($('#' + selector).is(':visible')) selector = "";

            if (selector === 'mobiliers') {
                $('#mobiliers').show();
            } else {
                $('#mobiliers').hide();
            }
        }

        show();
    </script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
          integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
            integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
            crossorigin=""></script>
    <link rel="stylesheet" href="https://mrmufflon.github.io/Leaflet.Coordinates/dist/Leaflet.Coordinates-0.1.3.css">
    <script src="https://mrmufflon.github.io/Leaflet.Coordinates/dist/Leaflet.Coordinates-0.1.3.min.js"></script>
    <script src="/js/map.js"></script>
    <script>
        map.addMarker("<?= $house->pos ?>");
    </script>
<?php require_once('templates/bottom.html');
$resPlayer->closeCursor();
$resHouse->closeCursor();
$mobiliers->closeCursor();