<?php
require_once('../init.php');
if (!Auth::isStaff()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous ne pouvez pas voir cette entreprise."];
    header('Location: /');
    exit();
}

$res = $DB->prepare('SELECT * FROM business WHERE id = :id') or die(print_r($DB->errorInfo()));
$res->bindParam(':id', $_GET['id'], PDO::PARAM_STR);
$res->execute();
$entreprise = $res->fetch(PDO::FETCH_OBJ);

$mobiliers = $DB->prepare('SELECT * FROM mobiliers WHERE pid = :pid') or die(print_r($DB->errorInfo()));
$mobiliers->bindParam(':pid', $entreprise->siret);
$mobiliers->execute();

if ($entreprise == NULL or empty($entreprise)) {
    $_SESSION['alert'] = ['type' => 'danger', 'message' => "Cette entreprise n'existe pas."];
    header('Location: /ServicesPublics/Entreprises.php');
    exit();
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
        header('Location: /ServicesPublics/Entreprise.php?id='.$entreprise->id);
    }
    $raison = str_replace("'", "\'", $_POST['message']);
    $pid = $_POST['pid'];
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
    for ($i = 0; $i < 8; $i++) $plaque .= '`'.$plaqueClean[$i].'`,';
    $plaque .= '`'.$plaqueClean[8].'`]"';

    try {
        $res = $DB->prepare('SELECT id FROM shop WHERE classname = :classname') or die(print_r($DB->errorInfo()));
        $res->bindParam(':classname', $classname);
        $res->execute();
        if (!isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Ce véhicule n'existe pas dans le shop."];
            header('Location: /ServicesPublics/Entreprise.php?id='.$entreprise->id);
        }

        $res = $DB->prepare('SELECT id FROM vehicles WHERE plate = :plaque AND id != :id') or die(print_r($DB->errorInfo()));
        $res->bindParam(':plaque', $plaque);
        $res->bindParam(':id', $idVehicule);
        $res->execute();
        if (isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Cette plaque existe déjà."];
            header('Location: /ServicesPublics/Entreprise.php?id='.$entreprise->id);
            exit();
        }

        if (Auth::isAdmin())
            $DB->exec("UPDATE vehicles SET classname='$classname', type='$type', pid = '$pid', plate = '$plaque', peinture = '$peinture', fuel='$fuel', insure = '$insure', insurecount='$insurecount' WHERE id = '$idVehicule'");
        else
            $DB->exec("UPDATE vehicles SET fuel='$fuel', insure = '$insure', insurecount='$insurecount' WHERE id = '$idVehicule'");

        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule de $name ($classname) modifié. [$raison]')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule modifié.'];
        header('Location: /ServicesPublics/Entreprise.php?id='.$entreprise->id);
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Erreur : '.$e->getMessage()];
        header('Location: /ServicesPublics/Entreprise.php?id='.$entreprise->id);
    }
}

$resVehicules = $DB->prepare('SELECT * FROM vehicles WHERE type = :siret') or die(print_r($DB->errorInfo()));
$resVehicules->bindParam(':siret', $entreprise->siret, PDO::PARAM_STR);
$resVehicules->execute();

$resLogs = $DB->prepare('SELECT * FROM fisc WHERE siret = :siret ORDER BY insert_time DESC LIMIT 0, 500') or die(print_r($DB->errorInfo()));
$resLogs->bindParam(':siret', $entreprise->siret, PDO::PARAM_STR);
$resLogs->execute();

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

if (isset($_POST) && isset($_POST['owner']) && Auth::isAdmin()) {
    $id = $_GET['id'];
    $owner = $_POST['owner'];
    $name = $_POST['name'];
    $pos = $_POST['pos'];
    $siret = $_POST['siret'];
    $capital = $_POST['capital'];
    $secteur = $_POST['secteur'];
    $membres = $_POST['membres'];
    $level = $_POST['level'];
    $active = $_POST['active'];
    $del = $_POST['del'];
    $paneluser = $_SESSION['Auth']['username'];

    $DB->exec("UPDATE business SET owner='$owner', name='$name', pos='$pos', siret='$siret' , capital='$capital', secteur='$secteur', membres='$membres', 
          level='$level', active='$active', del='$del' WHERE id='$id'");
    $name = str_replace(['``', '`', '[', ']', ',', '"'], [' ', '', '', '', '', ''], $entreprise->name);
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Entreprise ($name) modifiée.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Entreprise modifiée !'];
    header('Location: /ServicesPublics/Entreprise.php?id='.$id);
    exit();
}
require_once('../templates/top.php'); ?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">
                Entreprise - <?= str_replace(['``', '`', '[', ']', ',', '"'], [' ', '', '', '', '', ''], $entreprise->name) ?>
            </h2>
        </div>
    </header>
    <form method="post" autocomplete="off">
        <?php if (Auth::isAdmin()) { ?>
            <div class="row">
                <div class="col">
                    <button onclick="return confirm('Modifier l\'entreprise : ');" type="submit"
                            class="btn btn-outline-success m-3 float-right btn-lg">
                        <span class="fa fa-edit"></span> Modifier
                    </button>
                </div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <h5 class="card-header">Informations</h5>
                    <div class="card-body">
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Nom</strong></div>
                            <textarea <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Nom" type="text"
                                      name="name" class="form-control"><?= $entreprise->name; ?></textarea>
                            <div class="input-group-append input-group-text"><span class="fa fa-file-signature"></span>
                            </div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Active</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Active" type="text"
                                   name="active" value="<?= $entreprise->active; ?>" class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-check"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Supprimée</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Supprimée" type="text"
                                   name="del" value="<?= $entreprise->del; ?>" class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-trash"></span></div>
                        </div>
                        <?php $d = explode(",", explode('[', $entreprise->pos)[1]);
                        $xArma = pad(floor(intval($d[0]) / 100));
                        $yArma = pad(144 + floor((intval($d[1]) - 64) / 100)); ?>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Position</strong></div>
                            <input disabled title="Position" type="text" value="<?= $xArma ?>.<?= $yArma ?>" name="pos" class="form-control"/>
                            <div class="input-group-append input-group-text"><span class="fa fa-map-marked"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Position</strong></div>
                            <textarea <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Position" type="text"
                                      name="pos" class="form-control"><?= $entreprise->pos ?></textarea>
                            <div class="input-group-append input-group-text"><span class="fa fa-map-marked"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>SIRET</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="SIRET" type="text"
                                   name="siret" value="<?= $entreprise->siret; ?>" class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-file"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Capital</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Capital" type="text"
                                   name="capital" value="<?= $entreprise->capital; ?>"
                                   class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-euro-sign"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Secteur</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Secteur" type="text"
                                   name="secteur" value="<?= $entreprise->secteur; ?>"
                                   class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-folder"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Niveau</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Niveau" type="text"
                                   name="level" value="<?= $entreprise->level; ?>" class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-level-up-alt"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <h5 class="card-header">Membres</h5>
                    <div class="card-body">
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Propriétaire</strong></div>
                            <input <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> title="Propriétaire" type="text"
                                   name="owner" value="<?= $entreprise->owner; ?>"
                                   class="form-control">
                            <div class="input-group-append input-group-text"><span class="fa fa-user"></span></div>
                        </div>
                        <div class="form-group input-group">
                            <div class="input-group-prepend input-group-text"><strong>Employés</strong></div>
                            <textarea <?php if (!Auth::isAdmin()) { ?>disabled<?php } ?> style="height: 40vh;"
                                      title="Employés" type="text" name="membres"
                                      class="form-control"><?= $entreprise->membres ?></textarea>
                            <div class="input-group-append input-group-text"><span class="fa fa-users"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col">
            <div class="card mt-3">
                <h5 class="card-header" onclick="show('vehicules')">Véhicules <span
                            class="badge badge-dark"><?= $resVehicules->rowCount() ?></span></h5>
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
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($vehicule = $resVehicules->fetch(PDO::FETCH_OBJ)) { ?>
                            <tr <?php if ($vehicule->active) echo 'class="table-success"'; ?>>
                                <td title="ID: <?= $vehicule->id ?>" data-toggle="tooltip"><?php
                                    if ($vehicule->type == "Car") echo "<span class='fa fa-car'></span>";
                                    else if ($vehicule->type == "Ship") echo "<span class='fa fa-ship'></span>";
                                    else if ($vehicule->type == "Air") echo "<span class='fa fa-helicopter'></span>";
                                    else echo "<span class='fa fa-warehouse'></span>"; ?></td>
                                <td><?= str_replace(['V_ALF_', '_'], ['', ' '], $vehicule->classname) ?><?php if ($vehicule->active) echo "<span class='badge badge-success float-right ml-1'>Sorti</span>";
                                    if ($vehicule->isInHome) echo "<span class='badge badge-primary float-right ml-1'><span class='fa fa-home'></span></span>";
                                    if ($vehicule->peinture != '[]' and $vehicule->peinture != '"[]"') echo "<span class='badge badge-secondary float-right ml-1' data-toggle='tooltip' title='".str_replace([
                                            '"',
                                            '[', ',', ']', '`',
                                        ], ['', '', ', ', '', ''], $vehicule->peinture)."'><span class='fa fa-paint-brush'></span></span>";
                                    if (intval(str_replace(['"', ',', '[', ']', '.'], '', $vehicule->damage)) != 0) echo "<span class='badge badge-warning float-right'><span class='fa fa-angry'></span></span>";
                                    ?></td>
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
                                                <button class="btn btn-info"
                                                        onclick='editVehicule(<?= $vehicule->id ?>, "<?= $vehicule->pid ?>", "<?= $vehicule->type ?>",
                                                                "<?= $vehicule->classname ?>",
                                                                "<?= str_replace(['``', '`', '[', ']', ',', '"',], [' ', '', '', '', '', ''], $vehicule->plate) ?>",
                                                                (String.raw`<?= str_replace(['"', '`'], ['', '|'], $vehicule->peinture) ?>`).replace(/[|]/gi, "`").replace(/[\\]/gi,
                                                                "\\\\"), <?= $vehicule->fuel ?>,
                                                        <?= $vehicule->insure ?>,<?= $vehicule->insurecount ?>)'>
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <a onclick="return confirm('Supprimer le véhicule :');"
                                                   href="/Admin/deleteVehicule?id=<?= $vehicule->id ?>"
                                                   class="btn btn-sm btn-danger">
                                                    <span class="fa fa-trash"></span></a>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (Auth::isModo()) { ?>
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
                            </tr>
                            </thead>
                            <tbody>
                            <?php while ($mobilier = $mobiliers->fetch(PDO::FETCH_OBJ)) { ?>
                                <tr>
                                    <td style="text-align:center" title="ID: <?= $mobilier->id ?>" data-toggle="tooltip"><span class='fas fa-home'></span></td>
                                    <td><?= str_replace(['Land', 'ALF_', '_'], ['', '', ' '], $mobilier->classname) ?><br>(<?= $mobilier->classname ?>)</td>
                                    <td><?= $mobilier->pos ?></td>
                                    <td><?= getGear(str_replace(['"', '`', '[', ']', ','], ['', '', '', '', ' '], $mobilier->gear)) ?></td>
                                    <td><?= $mobilier->insert_time ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <div class="card">
                <h5 class="card-header" onclick="show('logs')">Factures <span
                            class="badge badge-dark"><?= $resLogs->rowCount() ?></span></h5>
                <div class="card-body" id="logs">
                    <table class="table card-text table-bordered table-hover table-responsive-sm">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Personne</th>
                            <th>Montant</th>
                            <th>Déclaré</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($log = $resLogs->fetch(PDO::FETCH_OBJ)) { ?>
                            <tr class="<?= !$log->isDeclare ? "table-warning" : "" ?>">
                                <td><?= $log->insert_time ?></td>
                                <td><?= $log->name ?></td>
                                <td><?= $log->paie ?> €</td>
                                <td><?= $log->isDeclare ? "Oui" : "Non" ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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
                                <input type="text" name="pid" id="pid" required class="form-control"/>
                            </div>
                            <div class="form-group">
                                <label for="classnameEdit">ClassName</label>
                                <div class="input-group">
                                    <input type="text" title="ClassName de véhicule valable (liste sur la page Dispo Véhicules)" name="classnameEdit" id="classnameEdit"
                                           required class="form-control"/>
                                    <div class="input-group-append">
                                        <a href="/Admin/Vehicules.php.php" target="_blank" class="btn btn-info"><span class="fa fa-list"></span></a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="typeEdit">Type/SIRET</label>
                                <input type="text" title="Type du véhicule/SIRET de l'entreprise" name="typeEdit" id="typeEdit" required class="form-control"/>
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
    <script>
        function editVehicule(id, pid, type, classname, plaque, peinture, fuel, insure, insurecount) {
            $('#editVehiculeForm').attr('action', "/ServicesPublics/Entreprise.php?id=<?= $entreprise->id?>&editVehicule=" + id);
            $('#pid').val(pid);
            $('#typeEdit').val(type);
            $('#classnameEdit').val(classname);
            $('#plate').val(plaque);
            $('#peinture').val(peinture);
            $('#fuel').val(fuel);
            $('#insure').val(insure);
            $('#insurecount').val(insurecount);
            $('#editVehiculeModal').modal();
        }

        function show(selector) {
            if ($('#' + selector).is(':visible')) selector = "";

            switch (selector) {
                case 'logs':
                    $('#logs').show();
                    $('#vehicules').hide();
                    $('#mobiliers').hide();
                    break;
                case 'mobiliers':
                    $('#logs').hide();
                    $('#vehicules').hide();
                    $('#mobiliers').show();
                    break;
                case 'vehicules':
                    $('#logs').hide();
                    $('#vehicules').show();
                    $('#mobiliers').hide();
                    break;
                default:
                    $('#logs').hide();
                    $('#vehicules').hide();
                    $('#mobiliers').hide();
                    break;
            }
        }

        show();
    </script>
<?php
$res->closeCursor();
$resVehicules->closeCursor();
include('../templates/bottom.html');
