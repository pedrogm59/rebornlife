<?php
include '../init.php';
$vehicles = $DB->prepare("SELECT * FROM vehicles_cop ORDER BY id DESC");
$vehicles->execute();

if (isset($_GET) and isset($_GET['vehicule']) and isset($_GET['sendHome'])) //Renvoi véhicule au garage
{
    $idVehicule = $_GET['vehicule'];
    $paneluser = $_SESSION['Auth']['username'];
    $name = $player->name;
    $DB->exec("UPDATE vehicles_cop SET active=0 WHERE id=$idVehicule;");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule GN ($idVehicule) renvoyé au garage.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule renvoyé au garage (au prochain reboot).'];
    header('Location: /Gendarmerie/Garage.php');
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
            header('Location: /Gendarmerie/Garage.php');
            exit();
        }

        $res = $DB->prepare('SELECT id FROM vehicles_cop WHERE plate = :plaque') or die(print_r($DB->errorInfo()));
        $res->bindParam(':plaque', $plaque);
        $res->execute();
        if (isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Cette plaque existe déjà."];
            header('Location: /Gendarmerie/Garage.php');
            exit();
        }

        $DB->exec("INSERT INTO vehicles_cop (classname, type, pid, alive, active, plate, gear, position, pos_save, pos_check, fuel, damage, isInHome, lockveh, locktime, insert_time) 
                                      VALUES ('$classname', '$type', '', 1, 0, '$plaque', '[]', '[]', '[]', '[]', 1, '[]', 0, 0, 0, '$insertTime');");
        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule GN ($classname) ajouté au garage. [$raison]')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule ajouté au garage GN.'];
        header('Location: /Gendarmerie/Garage.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Erreur : '.$e->getMessage()];
        header('Location: /Gendarmerie/Garage.php');
        exit();
    }
}
else if (isset($_POST) and isset($_GET['editVehicule']) and Auth::isModo()) //Modification véhicule
{
    $idVehicule = $_GET['editVehicule'];
    $res = $DB->prepare('SELECT classname FROM vehicles_cop WHERE id=:id') or die(print_r($DB->errorInfo()));
    $res->bindParam(':id', $idVehicule);
    $res->execute();
    $classname = $res->fetch(PDO::FETCH_ASSOC)['classname'];
    if (!isset($classname)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Ce véhicule n'existe pas."];
        header('Location: /Gendarmerie/Garage.php');
        exit();
    }
    $raison = str_replace("'", "\'", $_POST['message']);
    $peinture = '"'.$_POST['peinture'].'"';
    $fuel = $_POST['fuel'];
    $plaqueClean = $_POST['plate'];
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
            header('Location: /Gendarmerie/Garage.php');
            exit();
        }

        $res = $DB->prepare('SELECT id FROM vehicles_cop WHERE plate = :plaque AND id != :id') or die(print_r($DB->errorInfo()));
        $res->bindParam(':plaque', $plaque);
        $res->bindParam(':id', $idVehicule);
        $res->execute();
        if (isset($res->fetch(PDO::FETCH_ASSOC)['id'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Cette plaque existe déjà."];
            header('Location: /Gendarmerie/Garage.php');
            exit();
        }

        if (Auth::isAdmin())
            $DB->exec("UPDATE vehicles_cop SET classname='$classname', type='$type', plate = '$plaque', peinture = '$peinture', fuel='$fuel' WHERE id = '$idVehicule'");
        else
            $DB->exec("UPDATE vehicles_cop SET fuel='$fuel' WHERE id = '$idVehicule'");

        $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule GN ($classname) modifié. [$raison]')");
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Véhicule modifié.'];
        header('Location: /Gendarmerie/Garage.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Erreur : '.$e->getMessage()];
        header('Location: /Gendarmerie/Garage.php');
        exit();
    }
}

include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Gendarmerie - Garage <span class="badge badge-dark"><?= $vehicles->rowCount() ?></span></h2>
        </div>
    </header>
    <div class="btn btn-group">
        <?php if (Auth::isAdmin()) { ?>
            <button type="button" class="btn btn-outline-secondary btn-sm float-md-right" data-toggle="modal" data-target="#addVehiculeModal">
                <span class="fa fa-plus"></span> Ajouter
            </button>
        <?php } ?>
    </div>
    <table class="table card-text table-bordered table-hover table-responsive-sm bg-white">
        <thead>
        <tr>
            <th></th>
            <th>Modèle</th>
            <th>Essence</th>
            <th>Inventaire</th>
            <th>Plaque</th>
            <th>Date d'achat</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($vehicle = $vehicles->fetch(PDO::FETCH_OBJ)) { ?>
            <tr <?php if ($vehicle->active) echo 'class="table-success"'; ?>>
                <td title="ID: <?= $vehicle->id ?>" data-toggle="tooltip"><?php
                    if ($vehicle->type == "Car") echo "<span class='fa fa-car'></span>";
                    else if ($vehicle->type == "Ship") echo "<span class='fa fa-ship'></span>";
                    else if ($vehicle->type == "Air") echo "<span class='fa fa-helicopter'></span>";
                    else echo "<span class='fa fa-warehouse'></span>"; ?></td>
                <td><?= str_replace(['V_ALF_', 'ALF_', '_'], ['', '', ' '], $vehicle->classname) ?>
                    <?php
                    if ($vehicle->active) echo "<span class='badge badge-success float-right ml-1'>Sorti</span>";
                    if ($vehicle->isInHome and $vehicle->active) echo "<span class='badge badge-primary float-right ml-1'><span class='fa fa-home'></span></span>";
                    if ($vehicle->peinture != '[]' and $vehicle->peinture != '"[]"') echo "<span class='badge badge-secondary float-right ml-1' data-toggle='tooltip' title='".str_replace([
                            '"',
                            '[', ',', ']', '`',
                        ], ['', '', ', ', '', ''], $vehicle->peinture)."'><span class='fa fa-paint-brush'></span></span>";
                    if (intval(str_replace(['"', ',', '[', ']', '.'], '', $vehicle->damage)) != 0) echo "<span class='badge badge-warning float-right'><span class='fa fa-car-crash'></span></span>";
                    ?>
                </td>
                <td>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?= $vehicle->fuel * 100 ?>%;"></div>
                    </div>
                </td>
                <td><?= str_replace(['[', ']', '"', '`', ',', ', ,'], ['', '', '', '', ', ', ''], $vehicle->gear) ?></td>
                <td><?= str_replace(['``', '`', '[', ']', ',', '"'], [' ', '', '', '', '', ''], $vehicle->plate) ?></td>
                <td><?= $vehicle->insert_time ?></td>
                <td><?php if (Auth::isStaff()) { ?>
                        <div class="btn-group btn-group-sm">
                            <?php if ($vehicle->active == 0) { ?>
                                <a onclick="return confirm('Supprimer le véhicule :');" href="/Admin/deleteVehicule?id=<?= $vehicle->id ?>&garage=Gendarmerie"
                                   class="btn btn-sm btn-danger">
                                    <span class="fa fa-trash"></span></a>
                            <?php } else { ?>
                                <a href="/Gendarmerie/Garage.php?vehicule=<?= $vehicle->id ?>&sendHome" class="btn btn-primary"
                                   onclick="return confirm('Renvoyer le véhicule au garage (prochain reboot) : ');">
                                    <span class="fa fa-home"></span></a>
                            <?php } ?>
                            <button class="btn btn-info"
                                    onclick='editVehicule(<?= $vehicle->id ?>, "<?= $vehicle->type ?>", "<?= $vehicle->classname ?>",
                                            "<?= str_replace(['``', '`', '[', ']', ',', '"',], [' ', '', '', '', '', ''], $vehicle->plate) ?>",
                                            (String.raw`<?= str_replace(['"', '`'], ['', '|'], $vehicle->peinture) ?>`).replace(/[|]/gi, "`")
                                            .replace(/[\\]/gi, "\\\\"), <?= $vehicle->fuel ?>)'>
                                <i class="fa fa-edit"></i>
                            </button>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php if (Auth::isAdmin()) { ?>
    <div class="modal fade" id="addVehiculeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" action="/Gendarmerie/Garage.php?addVehicule">
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
        function editVehicule(id, type, classname, plaque, peinture, fuel) {
            $('#editVehiculeForm').attr('action', "/Gendarmerie/Garage.php?editVehicule=" + id);
            let types = ['Car', 'Air', 'Ship', type];

            $("#typeEdit").empty();
            $.each(types, function (key, t) {
                $("#typeEdit").append($('<option>', {value: t, text: t})).val(type);
            });
            $('#classnameEdit').val(classname);
            $('#plate').val(plaque);
            $('#peinture').val(peinture);
            $('#fuel').val(fuel);
            $('#editVehiculeModal').modal();
        }
    </script>
<?php
$vehicles->closeCursor();
include('../templates/bottom.html');