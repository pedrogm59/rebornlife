<?php
include '../init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = array('type' => 'warning', 'message' => "Vous n'avez pas accès à la liste des personnes les plus riches.");
    header('Location: /');
    exit();
}
$search = $DB->query("SELECT DISTINCT * FROM players INNER JOIN bank WHERE players.playerid = bank.playerid ORDER BY cash+livreta+livretb+livretc  DESC LIMIT 0, 150");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Joueurs les plus riches</h2>
        </div>
    </header>
<?php
$liquide = TRUE;
include('../templates/liste.php');
$search->closeCursor();
include('../templates/bottom.html');