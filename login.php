<?php
require_once('init.php');

if (Auth::isLogged()) {
    header('Location: /');
    exit();
}

require_once('templates/top.php');
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4 text-dark">Arma Life France - Administration</h2>
        </div>
    </header>
    <div class="row justify-content-center">
        <div class="col-sm-4 mt-5 pt-4 pb-3" style="background-color: rgba(73,73,73,0.8); color: white; border-radius: 20px;">
            <form method="post" class="form-horizontal" role="form">
                <?php if (!empty($_POST) && !empty($_POST['password']) && !empty($_POST['username'])) {
                    $username = $_POST['username'];
                    $password = hash('sha512', ($_POST['password']));
                    if (!empty($username) && !empty($password)) {
                        $tab_co = [
                            'username' => $username,
                            'password' => $password,
                        ];
                        $requ = $DB->prepare('SELECT * FROM users WHERE username=:username AND password=:password');
                        $requ->execute($tab_co);
                        $row = $requ->fetch(PDO::FETCH_OBJ);
                        if ($row != NULL) {
                            $_SESSION['Auth'] = [
                                'id'       => $row->id,
                                'username' => $row->username,
                                'password' => $row->password,
                                'role'     => $row->role,
                                'rcon'     => $row->rcon,
                            ];
                            header('Location: /');
                        }
                        else {
                            ?>
                            <div class="alert alert-warning text-center" role="alert">
                                <strong>Utilisateur non reconnu</strong>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
                <div class="form-group">
                    <label class="col-sm control-label" for="username">
                        <span class="fa fa-user"></span> Utilisateur</label>
                    <div class="col-sm">
                        <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm control-label" for="password">
                        <span class="fa fa-key"></span> Mot de Passe</label>
                    <div class="col-sm">
                        <input type="password" class="form-control" name="password" placeholder="Mot de passe"/>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100"><span class="fa fa-sign-in-alt"></span> Connexion</button>
            </form>
        </div>
    </div>
    <style>
        body {
            background-size : auto;
            background      : url("/img/background.jpg") no-repeat fixed center;
            color           : lightgrey;
        }
    </style>
<?php
include 'templates/bottom.html';
