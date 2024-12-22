<?php
include '../init.php';
if (!Auth::isModo()) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Vous n'avez pas accès aux disponibilités des véhicules."];
    header('Location: /');
    exit();
}

$vehicules = $DB->query("SELECT * FROM shop ORDER BY classname ASC");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Shop - Véhicules</h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <div class="btn-toolbar">
                <div class="input-group mr-2">
                    <input id="inputSearchVehicules" onkeyup="searchVehicule();" class="form-control" type="search" placeholder="Rechercher"
                           aria-label="Search">
                    <div class="input-group-append input-group-text" onclick="$('#inputSearchVehicules').val('').keyup();">
                        <span class="fa fa-search"></span>
                    </div>
                </div>
            </div>
            <table class="table table-responsive-sm table-bordered bg-white mt-2" id="tableVehicule">
                <thead>
                <tr>
                    <th>Modèle</th>
                    <th>ClassName</th>
                    <th>Disponibilité</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = $vehicules->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr <?php if ($row->dispo == 0) { ?> class="table-danger"<?php } ?>>
                        <td>
                            <?php if (Auth::isAdmin()) { ?>
                                <a href="/Admin/Vehicule.php?ClassName=<?= $row->classname ?>"><?= str_replace(['V_ALF_', 'ALF_', '_'], ['', '', ' '], $row->classname) ?></a>
                            <?php } else echo $row->classname; ?>
                        </td>
                        <td><em class="text-muted"><?= $row->classname ?></em></td>
                        <td><?= $row->dispo ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $('#inputSearchVehicules').val('');

        function searchVehicule() {
            let input = document.getElementById("inputSearchVehicules"),
                filter = input.value.toUpperCase(),
                table = document.getElementById("tableVehicule"),
                tr = table.getElementsByTagName("tr");

            for (let i = 0; i < tr.length; i++) {
                let classname = tr[i].getElementsByTagName("td")[0];
                if (classname) {
                    if (classname.innerHTML.toUpperCase().indexOf(filter) > -1) tr[i].style.display = "";
                    else tr[i].style.display = "none";
                }
            }
        }
    </script>
<?php
$vehicules->closeCursor();
include('../templates/bottom.html');