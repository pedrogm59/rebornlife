<?php
include '../init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = array('type' => 'warning', 'message' => "Vous n'avez pas accès à la liste des personnes.");
    header('Location: /');
    exit();
}
$search = $DB->query("SELECT DISTINCT * FROM players ORDER BY playtime DESC LIMIT 0, 150");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Joueurs les plus présents (Temps de jeu)</h2>
        </div>
    </header>
<?php
include('../templates/liste.php');
$search->closeCursor();
include('../templates/bottom.html');