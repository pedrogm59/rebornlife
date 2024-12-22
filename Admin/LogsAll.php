<?php
include '../init.php';
if (isset($_GET['q'])) $search_value = $_GET['q'];
else $search_value = "";

$admin_settings = array('AdminMap' => 0, 'GodMod' => 0, 'Invi' => 0);
$log_to_print = array();

if ($search_value != "") {
	$search = $DB->prepare("
		SELECT DISTINCT * 
		FROM logs_alf 
		WHERE (type = 'ADMIN' and name LIKE :search_value)
				OR (type = 'KNOCKOUT' and name LIKE :search_value)
				OR (type = 'MORT' and text LIKE :search_value)
		ORDER BY insert_time;");
	$search->bindValue(':search_value', '%'.$search_value.'%');
	$search->execute();

	while ($row = $search->fetch(PDO::FETCH_OBJ)) {
		if ($row->type == "ADMIN") {
			if ($row->text == 'A active le MapAdmin') $admin_settings['AdminMap'] = $row->insert_time;
			else if ($row->text == 'A desactive le MapAdmin') $admin_settings['AdminMap'] = 0;
			else if ($row->text == 'GodMod ON') $admin_settings['GodMod'] = $row->insert_time;
			else if ($row->text == 'GodMod OFF') $admin_settings['GodMod'] = 0;
			else if ($row->text == 'Est invisible') $admin_settings['Invi'] = $row->insert_time;
			else if ($row->text == 'Est maintenant visible') $admin_settings['Invi'] = 0;
		} else if ($admin_settings['AdminMap'] != 0) {
			if (strtotime($row->insert_time) - strtotime($admin_settings['AdminMap']) < 60 * 60 * 6) array_push($log_to_print, array($row, $admin_settings));
		} else if ($admin_settings['GodMod'] != 0) {
			if (strtotime($row->insert_time) - strtotime($admin_settings['GodMod']) < 60 * 60 * 6) array_push($log_to_print, array($row, $admin_settings));
		} else if ($admin_settings['Invi'] != 0) {
			if (strtotime($row->insert_time) - strtotime($admin_settings['Invi']) < 60 * 60 * 6) array_push($log_to_print, array($row, $admin_settings));
		}
	}
}

include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Logs Admin Tracking</h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <form method="get" id="searchLogs">
                <div class="input-group mb-2">
                    <input name="q" id="q" title="Recherche" placeholder="Pseudo de la personne à tracker" class="form-control"
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
        <div class="col">
            <table class="table table-responsive-sm table-bordered bg-white">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Info</th>
					<th>Admin Settings</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($log_to_print as &$row) { ?>
                    <tr <?php
                    if ($row[0]->type == "MORT") echo "class='table-danger'";
                    else if ($row[0]->type == "BANK") echo "class='table-primary'";
                    else if ($row[0]->type == "GIVE") echo "class='table-success'";
                    else if (in_array($row[0]->type, ["LEBONCOIN", "VENTE", "VEHICULE", "MAISON"])) echo "class='table-info'";
                    else if ($row[0]->type == "KNOCKOUT") echo "class='table-warning'";
                    ?>>
                        <td><a href="/joueur.php?id=<?= $row[0]->uid ?>"><?= $row[0]->name ?></td>
                        <td><?= $row[0]->insert_time ?></td>
                        <td><?= $row[0]->type ?></td>
                        <td><?= $row[0]->text ?></td>
						<td>
							<?php
							if ($row[1]['AdminMap'] != 0) echo '<span class="badge mr-1" style="background:#FF0000;">AdminMap depuis '.$row[1]['AdminMap'].'</span></br>';
							if ($row[1]['GodMod'] != 0) echo '<span class="badge mr-1" style="background:#96CA2D;">GodMod depuis '.$row[1]['GodMod'].'</span></br>';
							if ($row[1]['Invi'] != 0) echo '<span class="badge mr-1" style="background:#428BCA;">Invisible depuis '.$row[1]['Invi'].'</span>';
							?>
						</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
if ($search_value != "") $search->closeCursor();
include('../templates/bottom.html');