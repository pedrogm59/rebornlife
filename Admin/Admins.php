<?php
include '../init.php';
if (!Auth::isAdmin()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès à la liste des admins IG."];
    header('Location: /');
    exit();
}
$search = $DB->query("SELECT DISTINCT * FROM players WHERE adminlevel != '0' ORDER BY adminlevel DESC, name ASC");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Admins <span class="badge badge-dark"><?= $search->rowCount() ?></span></h2>
        </div>
    </header>
<?php
include('../templates/liste.php');
$search->closeCursor();
include('../templates/bottom.html');
