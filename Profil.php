<?php
include 'init.php';

if (isset($_POST) and isset($_POST['password']) and isset($_POST['password2']) and $_POST['password'] == $_POST['password2']) {
    $password = hash('sha512', ($_POST['password']));
    $id = $_SESSION['Auth']['id'];
    $DB->exec("UPDATE users SET password='$password' WHERE id='$id'");
    $_SESSION['alert'] = array('type' => 'success', 'message' => "Mot de passe modifié.");
    $_SESSION['Auth']['password'] = $password;
    header('Location: /Profil.php');
    exit();
}
include 'templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Panel - Profil</h2>
        </div>
    </header>
    <div class="row">
        <div class="col-md">
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text">
                            <span class="fa fa-key"></span>
                        </div>
                        <input title="Entrez votre nouveau mot de passe" class="form-control form-control-lg" type="password" name="password" id="password">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend input-group-text">
                            <span class="fa fa-key"></span>
                        </div>
                        <input title="Entrez votre nouveau mot de passe" class="form-control form-control-lg" type="password" name="password2" id="password2">
                    </div>
                </div>
                <button onclick="if ($('#password').val() !== $('#password2').val()) alert('Les mots de passe doivent être identiques.');
                        else return confirm('Modifier votre mot de  passe : ')" type="submit" class="btn btn-outline-primary">
                    <span class="fa fa-edit"></span> Modifier votre mot de passe
                </button>
        </div>
    </div>
<?php
include('templates/bottom.html');