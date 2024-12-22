<?php
include '../init.php';
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";
if (isset($_GET['page'])) $page = (int) $_GET['page'];
else $page = 1;
if ($page < 1) $page = 1;
$search = $DB->prepare("SELECT DISTINCT * FROM logs_alf WHERE (name LIKE :search_value OR uid LIKE :search_value OR type LIKE :search_value OR text LIKE :search_value) AND type != 'ADMIN' AND type != 'SERVICE' ORDER BY insert_time DESC LIMIT 200 OFFSET ".($page - 1) * 200 .";");
$search->bindValue(':search_value', '%'.$search_value.'%');
$search->execute();
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Logs</h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <form method="get" id="searchLogs">
                <div class="input-group mb-2">
                    <input name="q" id="q" title="Recherche" placeholder="Recherche" class="form-control"
                           autocomplete="off" type="search" value="<?= $search_value ?>"/>
                    <div class="input-group-append input-group-text"
                         onclick="$('#q').val(''); $('#searchLogs').submit();">
                        <span class="fa fa-search"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <nav class="col-auto mx-auto">
            <ul class="pagination">
                <li class="page-item<?php if ($page <= 1) { ?> disabled<?php } ?>">
                    <a class="page-link" href="/Admin/Logs.php?page=<?= $page - 1 ?>&q=<?= $search_value ?>"><< Précédent</a>
                </li>
                <li class="page-item disabled">
                    <a class="page-link" href="/#"><?= $page ?></a>
                </li>
                <li class="page-item<?php if ($search->rowCount() == 0 || $search->rowCount() < 200) { ?> disabled<?php } ?>">
                    <a class="page-link" href="/Admin/Logs.php?page=<?= $page + 1 ?>&q=<?= $search_value ?>">Suivant >></a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-responsive-sm table-bordered bg-white">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Info</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr <?php
                    if ($row->type == "MORT") echo "class='table-danger'";
                    else if ($row->type == "BANK") echo "class='table-primary'";
                    else if ($row->type == "GIVE") echo "class='table-success'";
                    else if (in_array($row->type, ["LEBONCOIN", "VENTE", "VEHICULE", "MAISON"])) echo "class='table-info'";
                    else if ($row->type == "KNOCKOUT") echo "class='table-warning'";
                    ?>>
                        <td><a href="/joueur.php?id=<?= $row->uid ?>"><?= $row->name ?></td>
                        <td><?= $row->insert_time ?></td>
                        <td><?= $row->type ?></td>
                        <td><?= $row->text ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <nav class="col-auto mx-auto">
            <ul class="pagination">
                <li class="page-item<?php if ($page <= 1) { ?> disabled<?php } ?>">
                    <a class="page-link" href="/Admin/Logs.php?page=<?= $page - 1 ?>&q=<?= $search_value ?>"><< Précédent</a>
                </li>
                <li class="page-item disabled">
                    <a class="page-link" href="/#"><?= $page ?></a>
                </li>
                <li class="page-item<?php if ($search->rowCount() == 0 || $search->rowCount() < 200) { ?> disabled<?php } ?>">
                    <a class="page-link" href="/Admin/Logs.php?page=<?= $page + 1 ?>&q=<?= $search_value ?>">Suivant >></a>
                </li>
            </ul>
        </nav>
    </div>
<?php
$search->closeCursor();
include('../templates/bottom.html');