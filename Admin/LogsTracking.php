<?php
include '../init.php';
if (!Auth::isAdmin()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès aux logs tracking."];
    header('Location: /');
    exit();
}
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";
if (isset($_GET['page'])) $page = (int) $_GET['page'];
else $page = 1;
if ($page < 1) $page = 1;
$search = $DB->prepare("
	SELECT DISTINCT 
		logs_alf.name AS logs_alf_name,
		logs_alf.insert_time AS logs_alf_insert_time,
		logs_alf.type AS logs_alf_type,
		logs_alf.text AS logs_alf_text,
		logs_alf.uid AS logs_alf_uid,
		logs_admin.name AS logs_admin_name,
		logs_admin.insert_time AS logs_admin_insert_time,
		logs_admin.type AS logs_admin_type,
		logs_admin.text AS logs_admin_text,
		(CASE WHEN (logs_admin.text LIKE '%MapAdmin%') THEN 1 ELSE 0 END) AS logs_admin_mapadmin,
		logs_admin.uid AS logs_admin_uid
	FROM logs_alf AS logs_admin
		LEFT JOIN (
			SELECT DISTINCT * 
			FROM logs_alf 
			WHERE type in ('MORT', 'KNOCKOUT')
		) AS logs_alf
		ON logs_alf.uid = logs_admin.uid
			and TIMEDIFF(logs_alf.insert_time, logs_admin.insert_time) < '02:00:00' 
	WHERE logs_admin.type = 'ADMIN' 
	ORDER BY logs_alf.insert_time DESC, logs_admin.insert_time DESC
	LIMIT 200 OFFSET ".($page - 1) * 200 .";");
	
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
                    <a class="page-link" href="/Admin/LogsTracking.php?page=<?= $page - 1 ?>&q=<?= $search_value ?>"><< Précédent</a>
                </li>
                <li class="page-item disabled">
                    <a class="page-link" href="/#"><?= $page ?></a>
                </li>
                <li class="page-item<?php if ($search->rowCount() == 0 || $search->rowCount() < 200) { ?> disabled<?php } ?>">
                    <a class="page-link" href="/Admin/LogsTracking.php?page=<?= $page + 1 ?>&q=<?= $search_value ?>">Suivant >></a>
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
					<th>Test 1</th>
					<th>Test 2</th>
					<th>Test 3</th>
					<th>Test 4</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = $search->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr <?php
                    if ($row->logs_alf_type == "MORT") echo "class='table-danger'";
                    else if ($row->logs_alf_type == "BANK") echo "class='table-primary'";
                    else if ($row->logs_alf_type == "GIVE") echo "class='table-success'";
                    else if (in_array($row->logs_alf_type, ["LEBONCOIN", "VENTE", "VEHICULE", "MAISON"])) echo "class='table-info'";
                    else if ($row->logs_alf_type == "KNOCKOUT") echo "class='table-warning'";
                    ?>>
                        <td><a href="/joueur.php?id=<?= $row->logs_alf_uid ?>"><?= $row->logs_alf_name ?></td>
                        <td><?= $row->logs_alf_insert_time ?></td>
                        <td><?= $row->logs_alf_type ?></td>
                        <td><?= $row->logs_alf_text ?></td>
						<td><?= $row->logs_admin_type ?></td>
						<td><?= $row->logs_admin_insert_time ?></td>
						<td><?= $row->logs_admin_text ?></td>
						<td><?= $row->logs_admin_mapadmin ?></td>
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
                    <a class="page-link" href="/Admin/LogsTracking.php?page=<?= $page - 1 ?>&q=<?= $search_value ?>"><< Précédent</a>
                </li>
                <li class="page-item disabled">
                    <a class="page-link" href="/#"><?= $page ?></a>
                </li>
                <li class="page-item<?php if ($search->rowCount() == 0 || $search->rowCount() < 200) { ?> disabled<?php } ?>">
                    <a class="page-link" href="/Admin/LogsTracking.php?page=<?= $page + 1 ?>&q=<?= $search_value ?>">Suivant >></a>
                </li>
            </ul>
        </nav>
    </div>
<?php
$search->closeCursor();
include('../templates/bottom.html');