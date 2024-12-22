<?php
require_once('init.php');
$resPlayer = $DB->prepare('SELECT count(*) AS nbr, SUM(playtime) AS sum_playtime, SUM(cash) AS sum_cash, SUM(isvalidate) AS nbr_wl FROM players') or die(print_r($DB->errorInfo()));
$resPlayer->execute();
$player = $resPlayer->fetch(PDO::FETCH_OBJ);

$resVisaAttente = $DB->prepare('SELECT count(*) AS nbr FROM players WHERE isValidate = 0') or die(print_r($DB->errorInfo()));
$resVisaAttente->execute();
$visaAttente = $resVisaAttente->fetch(PDO::FETCH_OBJ);

$resBank = $DB->prepare('SELECT SUM(livreta) AS sum_livreta, SUM(livretb) AS sum_livretb, SUM(livretc) AS sum_livretc FROM bank') or die(print_r($DB->errorInfo()));
$resBank->execute();
$banq = $resBank->fetch(PDO::FETCH_OBJ);

$vehicules = $DB->prepare("SELECT classname, count(classname) AS nbr FROM vehicles GROUP BY classname ORDER BY nbr DESC") or die(print_r($DB->errorInfo()));
$vehicules->execute();

$vehicules_tot = $DB->prepare("SELECT count(classname) AS nbr FROM vehicles") or die(print_r($DB->errorInfo()));
$vehicules_tot->execute();
$vehicule_tot = $vehicules_tot->fetch(PDO::FETCH_OBJ);

$houses = $DB->prepare('SELECT count(*) AS nbr FROM houses') or die(print_r($DB->errorInfo()));
$houses->execute();
$hous = $houses->fetch(PDO::FETCH_OBJ);

$mobilier = $DB->prepare('SELECT count(*) AS nbr FROM mobiliers') or die(print_r($DB->errorInfo()));
$mobilier->execute();
$mob = $mobilier->fetch(PDO::FETCH_OBJ);

require_once('templates/top.php');
?>
    <header class="row m-3">
        <div class="col-md align-content-center">
            <h2 class="text-center display-4">Panel Admin ALF</h2>
        </div>
    </header>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <h5 class="card-header">Informations sur les <strong>Joueurs</strong></h5>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><strong>Nombre de demandes de visa :</strong> <?= $player->nbr ?> Demandes</li> 
                        <li><strong>Nombre de joueurs validés :</strong> <?= $player->nbr_wl ?> Joueurs</li>
                        <li><strong>Nombre de visa en attente de validation :</strong> <?= $visaAttente->nbr ?> Visa</li>
                        <li><strong>Temps de jeu total :</strong> <?= number_format($player->sum_playtime / 60, 0, ",", "."); ?> heures
                                                                                                                                 -> <?= number_format($player->sum_playtime / 60 / 24, 0, ",", "."); ?>
                                                                                                                                 jours
                                                                                                                                 -> <?= number_format($player->sum_playtime / 60 / 24 / 31, 0, ",", "."); ?>
                                                                                                                                 mois
																																 -> <?= number_format($player->sum_playtime / 60 / 24 / 31 / 12, 0, ",", "."); ?>
                                                                                                                                 années
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <h5 class="card-header">Informations sur les <strong>Biens</strong></h5>
                <div class="card-body">
                    <strong>Nombre de véhicules achetés :</strong> <?= $vehicule_tot->nbr ?> Véhicules
                    <table class="table table-borderless table-sm">
                        <?php for ($i = 1; $i <= 5; $i++) {
                            $veh = $vehicules->fetch(PDO::FETCH_OBJ); ?>
                            <tr>
								<td></td>
                                <td><?= $i ?>.</td>
                                <td style="border-right: 2px solid black; width: 100px">
                                    <strong><?= str_replace(['V_ALF_', 'ALF_', '_'], ['', '', ' '], $veh->classname) ?></strong></td>
                                <td style="padding-left: 25px;"><?= $veh->nbr ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                    <div style="text-align: center; color: gray;">_____________________________________________</div><br>
                    <ul class="list-unstyled">
						<li><strong>Nombre de biens immobiliers achetés :</strong> <?= $hous->nbr ?> Biens</li>
						<li><strong>Nombre de meubles posés :</strong> <?= $mob->nbr ?> Meubles</li>
					</ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <h5 class="card-header">Informations <strong>Masse monétaire</strong></h5>
                <div class="card-body">
                    <div id="donutchart" style="width: 250px; height: 250px; margin-left: auto; margin-right: auto; position: relative;"></div>
                    <br/>
                    <ul class="list-unstyled">
                        <li><strong>Argent en liquide :</strong> <?= number_format($player->sum_cash, 0, ",", ".") ?> €</li>
                        <li><strong>Argent sur livret A :</strong> <?= number_format($banq->sum_livreta, 0, ",", ".") ?> €</li>
                        <li><strong>Argent sur livret B :</strong> <?= number_format($banq->sum_livretb, 0, ",", ".") ?> €</li>
                        <li><strong>Argent sur livret C :</strong> <?= number_format($banq->sum_livretc, 0, ",", ".") ?> €</li>
                        <li><strong>Masse monétaire totale :</strong> <?= number_format($player->sum_cash + $banq->sum_livreta + $banq->sum_livretb + $banq->sum_livretc, 0, ",", ".") ?> €
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load("current", {packages: ["corechart"]});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'compte');
            data.addColumn('number', 'montant');
            data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
            data.addRows([
                ['Liquide', <?= $player->sum_cash ?> +0, '<div style="font-size: 13px; padding:12px; white-space: nowrap;"><strong>Liquidité :</strong><br/>' + <?= $player->sum_cash ?> +' €</div>'],
                ['Livret A', <?= $banq->sum_livreta ?> +0, '<div style="font-size: 13px; padding:12px; white-space: nowrap;"><strong>Livret A :</strong><br/>' + <?= $banq->sum_livreta ?> +' €</div>'],
                ['Livret B', <?= $banq->sum_livretb ?> +0, '<div style="font-size: 13px; padding:12px; white-space: nowrap;"><strong>Livret B :</strong><br/>' + <?= $banq->sum_livretb ?> +' €</div>'],
                ['Livret C', <?= $banq->sum_livretc ?> +0, '<div style="font-size: 13px; padding:12px; white-space: nowrap;"><strong>Livret C :</strong><br/>' + <?= $banq->sum_livretc ?> +' €</div>']
            ]);

            var options = {
                title: 'Masse monétaire',
                legend: 'none',
                pieHole: 0.50,
                backgroundColor: 'transparent',
                chartArea: {left: 0, top: 10, bottom: 10, width: '100%', height: '100%'},
                fontSize: 13,
                tooltip: {ignoreBounds: true, isHtml: true},
            };

            new google.visualization.PieChart(document.getElementById('donutchart')).draw(data, options);
        }
    </script>