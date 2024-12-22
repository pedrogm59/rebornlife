<?php
include '../init.php';
if (!Auth::isStaff()) {
    $_SESSION['alert'] = array('type' => 'warning', 'message' => "Vous n'avez pas accès aux disponibilités des véhicules.");
    header('Location: /');
    exit();
}
$classname = $_GET['ClassName'];
$res = $DB->prepare('SELECT * FROM shop WHERE classname = :classname') or die(print_r($DB->errorInfo()));
$res->bindParam(':classname', $classname);
$res->execute();
$rows = $res->fetch(PDO::FETCH_OBJ);

if ($rows == NULL or empty($rows)) {
    $_SESSION['alert'] = array('type' => 'danger', 'message' => "Ce véhicule n'existe pas.");
    header('Location: /Admin/Vehicules.php');
    exit();
}

if (isset($_POST) && isset($_POST['dispo'])) {
    $dispo = $_POST['dispo'];
    $paneluser = $_SESSION['Auth']['username'];
    $DB->exec("UPDATE shop SET dispo='$dispo' WHERE classname='$classname'");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Véhicule ($classname) : Dispo => $dispo')");
    $_SESSION['alert'] = array('type' => 'success', 'message' => 'Disponibilité modifiée !');
    header('Location: /Admin/Véhicule?ClassName='.$classname);
    exit();
}
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">
                Shop - <?= str_replace(array('V_ALF_', '_'), array('', ' '), $classname) ?></h2>
        </div>
    </header>
    <div class="row">
        <div class="col-md">
            <form method="post" autocomplete="off">
                <div class="input-group">
                    <div class="input-group-prepend input-group-text">
                        <span class="fa fa-sort-amount-down"></span>
                    </div>
                    <input title="Disponibilité" class="form-control form-control-lg" type="text" name="dispo" id="dispo" value="<?= $rows->dispo ?>">
                    <div class="input-group-append">
                        <button onclick="return confirm('Modifier la disponibilité : ')" type="submit" class="btn btn-outline-primary">
                            <span class="fa fa-edit"></span>
                        </button>
                    </div>
                </div>
        </div>
    </div>
<?php
$res->closeCursor();
include('../templates/bottom.html');