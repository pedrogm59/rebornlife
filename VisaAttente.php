<?php
include 'init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès aux visa en attente."];
    header('Location: /');
    exit();
}

$search = $DB->prepare("SELECT DISTINCT * FROM players WHERE isValidate = 0");
$search->execute();

include 'templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Visa en attente</h2>
        </div>
    </header>
<?php
include('templates/listevisa.php');
$search->closeCursor();
include('templates/bottom.html');