<?php
require_once '../init.php';
require_once 'functions.php';
if (!Auth::isAdmin()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès à la liste des commandes."];
    header('Location: /');
    exit();
}

$paneluser = $_SESSION['Auth']['username'];
if (isset($_POST['name']) and isset($_POST['response'])) {
    $response = addslashes($_POST["response"]);
    $name = $_POST["name"];
    $DB->exec("INSERT INTO command (name, response) VALUES ('$name', '$response')");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Ajout commande [$name]')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Commande ajoutée !'];
    header('Location: /Console/Commandes.php');
    exit();
}
else if (isset($_GET["deleteCommand"]) and isset($_GET["name"])) {
    $name = $_GET["name"];
    $DB->exec("DELETE FROM command WHERE name ='$name'");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Suppression commande [$name]')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Commande supprimée.'];
    header('Location: /Console/Commandes.php');
    exit();
}

$commands = $DB->query("SELECT * FROM command");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col align-content-center">
            <h2 class="text-center display-4">Console</h2>
        </div>
    </header>
    <div class="row">
        <div class="col-md-6">
            <h3>Commandes
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#commandModal"><i class="fas fa-plus"></i> Ajouter</button>
                <a target="_blank" href="Console.html" class="btn btn-success float-right"><i class="fas fa-terminal"></i> Console</a>
            </h3>
            <table class="table table-responsive-sm table-bordered bg-white table-hover">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Réponse</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($command = $commands->fetch(PDO::FETCH_OBJ)): ?>
                    <tr>
                        <td><?= $command->name ?></td>
                        <?php if (isset($functions[$command->response])): ?>
                            <td class="text-info font-italic">[<?= $command->response ?>] <?= $functions[$command->response] ?></td>
                        <?php else: ?>
                            <td><?= $command->response ?></td>
                        <?php endif; ?>
                        <td>
                            <a href="?deleteCommand&name=<?= $command->name ?>" class="btn btn-outline-danger" onclick="confirm('Supprimer la commande [<?= $command->name ?>]: ')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h3>Fonctions</h3>
            <table class="table table-responsive-sm table-bordered bg-white table-hover">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Descriptions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($functions as $key => $value): ?>
                    <tr>
                        <td><?= $key ?></td>
                        <td><?= $value ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="commandModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form method="post" action="/Console/Commandes.php" id="commandForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ajout/modification de commande</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Commande</label>
                            <div class="input-group">
                                <input type="text" maxlength="255" title="Commande à taper dans la console." name="name" id="name" required class="form-control"/>
                            </div>
                            <small class="form-text text-warning">Doit être un mot uniquement.</small>
                        </div>
                        <div class="form-group">
                            <label for="response">Réponse</label>
                            <div class="input-group">
                                <input type="text" maxlength="255" title="Réponse qui sera affichée." name="response" id="response" required class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
$commands->closeCursor();
include('../templates/bottom.html');