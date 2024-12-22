<?php

$apiKey = "u923457-a86c02ec3003f11c0f44e6d5";
$url = "https://api.uptimerobot.com/v2/";
$action = "getMonitors";

try {
    $data = file_get_contents($url.$action, FALSE, stream_context_create([
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query([
                "api_key"              => $apiKey,
                "format"               => "json",
                "custom_uptime_ratios" => "1-7-30",
            ]),
        ],
    ]));
    if ($data == FALSE) throw new Exception();
    $data = json_decode($data, TRUE);
    if (count($data["monitors"]) == 0) throw new Exception();
} catch (Exception $e) {
    header("Location: https://stats.uptimerobot.com/KAwKxhq8JW");
    exit();
} ?>
<table>
    <thead>
    <tr>
        <th>Nom</th>
        <th>URL</th>
        <th>Status</th>
        <th>24h</th>
        <th>7 jours</th>
        <th>1 mois</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data["monitors"] as $monitor): ?>
        <tr>
            <td><?= $monitor["friendly_name"] ?></td>
            <td><?= $monitor["url"] ?></td>
            <td><?php if ($monitor["status"] == 2): ?>Opérationnel
                <?php elseif ($monitor["status"] == 8): ?>Quelques pertes
                <?php elseif ($monitor["status"] == 9): ?>Hors Service
                <?php else: ?>Pause/Maintenance
                <?php endif; ?>
            </td>
            <?php $ratios = explode('-', $monitor["custom_uptime_ratio"]) ?>
            <td><?= $ratios[0] ?> %</td>
            <td><?= $ratios[1] ?> %</td>
            <td><?= $ratios[2] ?> %</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>