<?php
require_once('../../init.php');
if (!Auth::isAdmin()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès à la liste des utilisateurs du Panel."];
    header('Location: /');
    exit();
}

if (isset($_GET) && isset($_GET['name']) && isset($_GET['id']) && isset($_GET['delete'])) {
    $name = $_GET['name'];
    $id = $_GET['id'];
    $paneluser = $_SESSION['Auth']['username'];
    $DB->exec("DELETE FROM users WHERE id = $id");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Utilisateur ($name) supprimé.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Utilisateur supprimé.'];
    header('Location: /Admin/Panel/Utilisateurs.php');
    exit();
}
else if (isset($_GET) and isset($_GET['name']) and isset($_GET['level']) and isset($_GET['id'])) {
    $name = $_GET['name'];
    $level = $_GET['level'];
    $id = $_GET['id'];
    $paneluser = $_SESSION['Auth']['username'];
    $DB->exec("UPDATE users SET role=$level WHERE id=$id");
    if ($level == 4) $DB->exec("UPDATE users SET rcon=1 WHERE id=$id");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Utilisateur ($name) passé niveau $level.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Utilisateur modifié.'];
    header('Location: /Admin/Panel/Utilisateurs.php');
    exit();
}
else if (isset($_GET) and isset($_GET['name']) and isset($_GET['rcon']) and isset($_GET['id'])) {
    $name = $_GET['name'];
    $rcon = $_GET['rcon'];
    $id = $_GET['id'];
    $paneluser = $_SESSION['Auth']['username'];
    $message = $rcon == 1 ? "RCon activé" : "RCon désactivé";
    $DB->exec("UPDATE users SET rcon=$rcon WHERE id=$id");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Utilisateur ($name) : $message.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => $message.' pour '.$name];
    header('Location: /Admin/Panel/Utilisateurs.php');
    exit();
}

$search = $DB->query("SELECT * FROM users WHERE role != '0' ORDER BY role DESC");
require_once('../../templates/top.php');
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Panel - Utilisateurs <span class="badge badge-dark"><?= $search->rowCount() ?></span></h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <?php if (Auth::isAdmin()) { ?>
                <a href="/Admin/Panel/nouvelUtilisateur.php" class="btn btn-outline-primary m-2 float-right">
                    <span class="fa fa-plus"></span> Nouvel utilisateur
                </a>
            <?php } ?>
            <table class="table table-responsive-sm table-bordered bg-white">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Niveau</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr>
                        <td><?= $row->username ?>
                            <?php if ($row->rcon == 1 and $row->role != 4) { ?>
                                <span class="badge badge-info">RCON</span>
                            <?php } ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <?php
                                switch ($row->role) {
                                    case '1':
                                        echo '<button class="btn text-white dropdown-toggle" style="background-color: #dc9f00;" type="button" data-toggle="dropdown">Opérateur</button>';
                                        break;
                                    case '2':
                                        echo '<button class="btn text-white dropdown-toggle" style="background-color: #1cdc00;" type="button" data-toggle="dropdown">Staff 1</button>';
                                        break;
                                    case '3':
                                        echo '<button class="btn text-white dropdown-toggle" style="background-color: #0003dc;" type="button" data-toggle="dropdown">Staff 2</button>';
                                        break;
                                    case '4':
                                        echo '<button class="btn text-white dropdown-toggle" style="background-color: #dc0002;" type="button" data-toggle="dropdown">Admin</button>';
                                        break;
                                    default:
                                        echo '<button class="btn btn-outline-danger text-white dropdown-toggle" type="button" data-toggle="dropdown">Autre (".$row->role.")</button>';
                                        break;
                                }
                                ?>
                                <div class="dropdown-menu">
                                    <?php foreach (['Opérateur', 'Staff 1', 'Staff 2', 'Admin'] as $level => $name) { ?>
                                        <a onclick="return confirm('Confirmer le role <?= $name ?> :');" href="/Admin/Panel/Utilisateurs.php?id=<?= $row->id ?>&name=<?= $row->username
                                        ?>&level=<?= $level + 1 ?>"
                                           class="dropdown-item"><?=
                                            $name ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td>
							<a href="/Admin/Panel/Utilisateurs.php?id=<?= $row->id ?>&name=<?= $row->username ?>&delete" onclick="return confirm('Supprimer ' +
							 'l\'utilisateur : ');"
							   class="btn btn-danger">
								<span class="fa fa-trash"></span> Supprimer
							</a>
							<?php if ($row->role != 4) { ?>
                                <?php if ($row->rcon == 0) { ?>
                                    <a href="/Admin/Panel/Utilisateurs.php?id=<?= $row->id ?>&name=<?= $row->username ?>&rcon=1"
                                       onclick="return confirm('Confirmer l\'accès au RCON : ');"
                                       class="btn btn-info">
                                        <span class="fa fa-user-astronaut"></span> Activer RCon
                                    </a>
                                <?php } else { ?>
                                    <a href="/Admin/Panel/Utilisateurs.php?id=<?= $row->id ?>&name=<?= $row->username ?>&rcon=0"
                                       onclick="return confirm('Confirmer la désactivation de l\'accès au RCON : ');"
                                       class="btn btn-warning">
                                        <span class="fa fa-eject"></span> Désactiver RCon
                                    </a>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
$search->closeCursor();
require_once('../../templates/bottom.html');