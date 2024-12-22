<div class="row">
    <div class="col">
        <table class="table table-responsive-sm table-hover table-bordered bg-white">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Validé</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $search->fetch(PDO::FETCH_OBJ)) {
                ?>
                <tr onclick="window.location.href='/joueur.php?id=<?= $row->playerid ?>';">
                    <td><a href="/joueur.php?id=<?= $row->playerid ?>"><?= $row->name ?></a></td>
                    <td class="text-center"><?php if ($row->isValidate == 0) echo '<span class="badge badge-dark" title="Visa en attente" data-toggle="tooltip">N</span>'; 
						else if ($row->isValidate == 2) echo '<span class="badge badge-danger" title="Visa bloqué" data-toggle="tooltip">X</span>';
						else echo '<span class="badge badge-success" title="Visa validé" data-toggle="tooltip">V</span>'; ?></td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    </div>
</div>
