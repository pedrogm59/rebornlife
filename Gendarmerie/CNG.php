<?php
include '../init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = array('type' => 'warning', 'message' => "Vous n'avez pas accès au CNG.");
    header('Location: /');
    exit();
}

if (isset($_POST) and isset($_POST['amount']) and Auth::isStaff()) {
    $bank = $_POST['amount'];
    $paneluser = $_SESSION['Auth']['username'];
    $DB->exec("UPDATE cng SET bank='$bank' WHERE id=1");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','CNG modifié.')");
    $_SESSION['alert'] = array('type' => 'success', 'message' => "CNG modifié.");
    header('Location: /Gendarmerie/CNG.php');
    exit();
}
$CNG = $DB->query('SELECT * FROM cng WHERE id = 1');
$search = $DB->query("SELECT * FROM cng_list ORDER BY insert_time DESC LIMIT 50;");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Gendarmerie - CNG</h2>
        </div>
    </header>
    <div class="row">
        <div class="col-md">
            <form id="update_cng" name="update_cng" method="post" autocomplete="off">
                <div class="input-group">
                    <div class="input-group-prepend input-group-text">
                        <span class="fa fa-euro-sign"></span>
                    </div>
                    <input title="CNG" class="form-control form-control-lg" <?php if (!Auth::isStaff()) { ?> disabled <?php } ?> type="text" name="amount" id="amount"
                           value="<?= $CNG->fetch()['bank'] ?>">
                    <div class="input-group-append">
                        <button onclick="return confirm('Modifier la CNG : ')" type="submit" class="btn btn-outline-primary<?php if (!Auth::isStaff()) { ?> disabled <?php }
                        ?>">
                            <span class="fa fa-edit"></span>
                        </button>
                    </div>
                </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-md">
            <table class="table table-bordered bg-white">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Somme</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr>
                        <td><a href="/joueur.php?id=<?= $row->uid ?>"><?= $row->name ?></a></td>
                        <td><?= $row->value ?></td>
                        <td><?= $row->insert_time ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
$CNG->closeCursor();
include('../templates/bottom.html');