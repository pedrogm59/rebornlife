<?php
include '../init.php';
$search = $DB->query("SELECT DISTINCT * FROM players WHERE coplevel != '0' ORDER BY coplevel DESC, name ASC");
include '../templates/top.php';
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Gendarmerie <span class="badge badge-dark"><?= $search->rowCount() ?></span></h2>
        </div>
    </header>
    <div class="row">
        <div class="col">
            <table class="table table-responsive-sm table-hover table-bordered bg-white">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Validé</th>
                    <th>Temps de jeu</th>
                    <th>Temps de service <em class="small">(7 derniers jours)</em></th>
                    <th>Argent</th>
                    <th>Donateur</th>
                    <th>Grade</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $search->fetch(PDO::FETCH_OBJ)) {
                    $row2 = $DB->query("SELECT DISTINCT * FROM bank WHERE playerid = '$row->playerid'")->fetch(PDO::FETCH_OBJ);
                    $res6 = $DB->prepare('SELECT * FROM copservice WHERE name = :playername ORDER BY insert_time ASC') or die(print_r($DB->errorInfo()));
                    $res6->bindParam(':playername', $row->name);
                    $res6->execute();
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
                    $precedent = ".";
                    $dateDebut = "";
                    $heures = 0;
                    while ($rowS = $res6->fetch(PDO::FETCH_OBJ)) {
                        if (strtolower($precedent[0]) == "p" and strtolower($rowS->text[0]) == "t") {
                            $date = (new DateTime($rowS->insert_time))->diff(new DateTime($dateDebut));
                            $dH = $date->days * 24 + $date->h + $date->i / 60;
                            $heures += $dH;
                        }
                        $precedent = $rowS->text;
                        $dateDebut = $rowS->insert_time;
                    }
                    ?>
                    <tr onclick="window.location.href='/joueur.php?id=<?= $row->playerid ?>';">
                        <td><a href="/joueur.php?id=<?= $row->playerid ?>"><?= $row->name ?></a></td>
                        <td class="text-center"><?php if ($row->isValidate == 0) echo '<span class="badge badge-dark" title="Visa en attente" data-toggle="tooltip">N</span>';
                            else if ($row->isValidate == 2) echo '<span class="badge badge-danger" title="Visa bloqué" data-toggle="tooltip">X</span>';
                            else echo '<span class="badge badge-success" title="Visa validé" data-toggle="tooltip">V</span>'; ?></td>
                        <td class="text-right"><?= number_format($row->playtime / 60, 0, ",", ".") ?> heures</td>
                        <td class="text-right"><?= floor($heures) ?> h <?= round(($heures - floor($heures)) * 60, 0) ?> mn</td>
                        <td class="text-right"><?php if (Auth::isModo()) echo number_format($livreta + $livretb + $livretc + $row->cash, 0, ",", ".").' €'; ?></td>
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
<?php
$search->closeCursor();
include('../templates/bottom.html');