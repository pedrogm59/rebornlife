<?php
include 'init.php';
$search = $DB->query("SELECT DISTINCT * FROM players WHERE isPremium = 1 ORDER BY duredon ASC, name ASC");
include 'templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Donateurs <span class="badge badge-dark"><?= $search->rowCount() ?></span></h2>
        </div>
    </header>
<?php
include('templates/liste.php');
$search->closeCursor();
include('templates/bottom.html');