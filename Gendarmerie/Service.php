<?php
include '../init.php';
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";
$search = $DB->prepare("SELECT DISTINCT * FROM copservice JOIN players ON copservice.name = players.name WHERE players.name LIKE :search_value ORDER BY id DESC LIMIT 0, 200");
$search->bindValue(':search_value', '%'.$search_value.'%');
$search->execute();
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Gendarmerie - Service</h2>
        </div>
    </header>
    <form method="get" id="searchService">
        <div class="input-group mb-2">
            <input name="q" id="q" title="Recherche PDS et FDS" placeholder="Recherche" class="form-control" autocomplete="off" type="search" value="<?= $search_value ?>"/>
            <div class="input-group-append input-group-text" onclick="$('#q').val(''); $('#searchService').submit();">
                <span class="fa fa-search"></span>
            </div>
        </div>
    </form>
    <table class="table table-responsive-sm table-bordered bg-white">
        <thead>
        <tr>
            <th>Nom</th>
            <th>Date</th>
            <th>Contenu</th>
        </tr>
        </thead>
        <tbody>
        <?php
        while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
            <tr <?php if (strtolower($row->text[0]) == "p") { ?> class="table-success"
            <?php } else if (strtolower($row->text[0]) == "t") { ?> class="table-warning"<?php } ?>>
                <td><a href="/joueur.php?id=<?= $row->playerid ?>"><?= $row->name ?></a></td>
                <td><?= $row->insert_time ?></td>
                <td><?= $row->text ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php
$search->closeCursor();
include('../templates/bottom.html');