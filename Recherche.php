<?php
include 'init.php';

$search = $DB->prepare("SELECT DISTINCT * FROM players WHERE name LIKE :search_value OR playerid LIKE :search_value LIMIT 100");
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";
$search->bindValue(':search_value', '%'.$search_value.'%');
$search->execute();
if ($search->rowCount() == 1) {
    $id = $search->fetch(PDO::FETCH_OBJ)->playerid;
    header("Location: /joueur.php?id=".$id);
    exit();
}

include 'templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Recherche</h2>
        </div>
    </header>
<?php
include('templates/liste.php');
$search->closeCursor();
include('templates/bottom.html');