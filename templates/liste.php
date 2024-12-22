<div class="row">
    <div class="col">
        <table class="table table-responsive-sm table-hover table-bordered bg-white">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Validé</th>
                <th>Temps de jeu</th>
                <?php if (Auth::isModo()): ?>
                    <th>Argent</th>
                    <?php if (!empty($liquide)): ?>
                        <th>Liquide</th>
                    <?php endif; ?>
                <?php endif; ?>
                <th>Donateur</th>
                <th>Grade</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $search->fetch(PDO::FETCH_OBJ)) {
                $row2 = $DB->query("SELECT DISTINCT * FROM bank WHERE playerid = '$row->playerid'")->fetch(PDO::FETCH_OBJ);
                if ($row2 != NULL and !empty($row2)) {
                    $livreta = $row2->livreta;
                    $livretb = $row2->livretb;
                    $livretc = $row2->livretc;
                }
                else {
                    $livreta = 0;
                    $livretb = 0;
                    $livretc = 0;
                }
                ?>
                <tr onclick="window.location.href='/joueur.php?id=<?= $row->playerid ?>';">
                    <td><a href="/joueur.php?id=<?= $row->playerid ?>"><?= $row->name ?></a></td>
                    <td class="text-center"><?php if ($row->isValidate == 0) echo '<span class="badge badge-dark" title="Visa en attente" data-toggle="tooltip">N</span>'; 
						else if ($row->isValidate == 2) echo '<span class="badge badge-danger" title="Visa bloqué" data-toggle="tooltip">X</span>';
						else echo '<span class="badge badge-success" title="Visa validé" data-toggle="tooltip">V</span>'; ?></td>
                    <td class="text-right"><?= number_format($row->playtime / 60, 0, ",", ".") ?> heures</td>
                    <?php if (Auth::isModo()): ?>
                        <td class="text-right"><?= number_format($livreta + $livretb + $livretc + $row->cash, 0, ",", ".") ?> €</td>
                        <?php if (!empty($liquide)): ?>
                            <td class="text-right"><?= $row->cash ?> €</td>
                        <?php endif; ?>
                    <?php endif; ?>
                    <td class="text-center"><?php if ($row->isPremium != 0) echo '<span class="badge" style="background:#D9534F; width:80px;">'.$row->duredon.' jours</span>' ?></td>
                    <td>
                        <?php
                        if ($row->coplevel != '0') echo '<span class="badge mr-1" style="background:#428BCA;">Gendarme - '.$gradesGN[$row->coplevel].'</span>';
                        if ($row->mediclevel != '0') echo '<span class="badge mr-1" style="background:#FF0000;">Pompier - '.$gradesPompier[$row->mediclevel].'</span>';
                        if ($row->publique != '0') echo '<span class="badge mr-1" style="background:#96CA2D;">S.Public - '.$gradesSP[$row->publique].'</span>';
                        if ($row->penit != '0') echo '<span class="badge mr-1" style="background:#a19d98;">Prison - '.$gradesAP[$row->penit].'</span>';
                        if ($row->coplevel = '0' and $row->mediclevel = '0' and $row->publique = '0' and $row->penit = '0')
                            echo '<span class="badge mr-1" style="background-color:#5CB85C;">Civil</span>';
                        if ($row->adminlevel != '0') echo '<span class="badge" style="background:#F0AD4E;">Admin - '.$row->adminlevel.'</span>';
                        ?>
                    </td>
                </tr>
                <?php
            } ?>
            </tbody>
        </table>
    </div>
</div>
