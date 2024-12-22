<?php
include '../../init.php';

if (isset($_POST) and isset($_POST['username']) and isset($_POST['password'])) {
    $password = hash('sha512', ($_POST['password']));
    $username = $_POST['username'];
    $id = $_SESSION['Auth']['id'];
    $paneluser = $_SESSION['Auth']['username'];
    $DB->exec("INSERT INTO users(username, password, role, rcon) VALUES ('$username', '$password', '1', 0)");
    $DB->exec("INSERT INTO logs_panel (username, texte) VALUES ('$paneluser','Compte panel ($username) crée.')");
    $_SESSION['alert'] = ['type' => 'success', 'message' => "Utilisateur créé."];
    $_SESSION['Auth']['password'] = $password;
    header('Location: /Admin/Utilisateurs.php');
    exit();
}
include '../../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Panel - Nouvel utilisateur</h2>
        </div>
    </header>
    <div class="row">
        <div class="col-md">
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username" class="font-weight-bold">Nom</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text">
                            <span class="fa fa-user"></span>
                        </div>
                        <input title="Nom sur le panel" class="form-control form-control-lg" type="text" name="username" id="username">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="font-weight-bold">Mot de passe</label>
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text">
                            <span class="fa fa-key"></span>
                        </div>
                        <input title="Entrez le mot de passe" class="form-control form-control-lg" type="password" name="password" id="password">
                    </div>
                </div>
                <button onclick="return confirm('Créer l\'utilisateur : ')" type="submit" class="btn btn-outline-primary">
                    <span class="fa fa-save"></span> Nouvel utilisateur
                </button>
        </div>
    </div>
<?php
include('../../templates/bottom.html');