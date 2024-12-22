<?php
require_once('init.php');

if (!isset($_POST) or !isset($_POST['token']) or $_POST['token'] != "hpJUjgbSKUIbgb656") {
    header('Location: /login');
    exit();
}
else if (!isset($_POST['type'])) {
    header('Location: /');
    exit();
}

//Function to encode plaint text into ASCII array for Arma display
function encode($message) {
    if ($message == "")
        return "[]";

    $message = iconv("utf-8", "ascii//TRANSLIT", $message);
    $message = strtr($message, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    $encoded = "[";
    for ($i = 0; $i < strlen($message); $i++)
        $encoded .= strval(ord($message[$i])).',';
    $encoded[strlen($encoded) - 1] = ']';

    return $encoded;
}

$time = date("Y-m-d H:i:s");
try {
    switch ($_POST['type']) {
        case "userCheck":
            if (isset($_POST["data"])) {
                $resPlayer = $DB->prepare('SELECT isvalidate, lastplayed FROM players WHERE name = :userName ORDER BY lastplayed ASC') or die(print_r($DB->errorInfo()));
                $resPlayer->bindParam(':userName', $_POST["data"]);
                $resPlayer->execute();
                if ($resPlayer->rowCount() > 0) {
                    $p = $resPlayer->fetch(PDO::FETCH_ASSOC);

                    if ($p['isvalidate'] == 1)
                        $message = ["result" => $p["lastplayed"]];
                    else
                        $message = ["result" => "Not validated."];
                }
                else $message = ["result" => "Not found."];
            }
            else if (isset($_POST["players"])) {
                $players = json_decode($_POST["players"], TRUE);
                $result = [];
                foreach ($players as $player) {
                    $resPlayer = $DB->prepare('SELECT isvalidate, lastplayed FROM players WHERE name = :userName ORDER BY lastplayed ASC') or die(print_r($DB->errorInfo()));
                    $resPlayer->bindParam(':userName', $player["name"]);
                    $resPlayer->execute();
                    if ($resPlayer->rowCount() > 0) {
                        $p = $resPlayer->fetch(PDO::FETCH_ASSOC);
                        if ($p['isvalidate'] == 1)
                            $m = $p["lastplayed"];
                        else
                            $m = "Not validated.";
                    }
                    else
                        $m = "Not found.";

                    $result[] = [
                        "name"     => $player["name"],
                        "id"       => $player["id"],
                        "result"   => $m,
                        "category" => $player["category"],
                    ];
                }

                $message = ["result" => $result];
            }

            break;
        case "userService":
            $name = $_POST['data'];
            $data = [];
            $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text FROM copservice WHERE name = '$name' ORDER BY id ASC LIMIT 0, 200");
            $search->execute();
            $data += $search->fetchAll(PDO::FETCH_ASSOC);
            $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text FROM medservice WHERE name = '$name' ORDER BY id ASC LIMIT 0, 200");
            $search->execute();
            $data += $search->fetchAll(PDO::FETCH_ASSOC);
//            $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text FROM logs_alf WHERE name = '$name' AND text LIKE '%service%' ORDER BY id ASC LIMIT 0, 200");
//            $search->execute();
//            $data += $search->fetchAll(PDO::FETCH_ASSOC);
            $search = $DB->prepare("SELECT insert_time AS time, text FROM logs_alf WHERE type = 'SERVICE' AND name = '$name' ORDER BY id ASC LIMIT 0, 200");
            $search->execute();
            $data += $search->fetchAll(PDO::FETCH_ASSOC);

            $message = ["result" => $data];
            break;
        case "service":
            switch ($_POST['data']) {
                case "Gendarmerie":
                    $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text, name FROM copservice ORDER BY id ASC LIMIT 0, 10000");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "Pompier":
                    $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text, name FROM medservice ORDER BY id ASC LIMIT 0, 10000");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "Communes":
                    $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text, name FROM logs_alf WHERE type = 'SERVICE' AND text LIKE '%service Penit.' ORDER BY id ASC LIMIT 0, 10000");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                default:
                    $search = $DB->prepare("SELECT DISTINCT insert_time AS time, text, name FROM logs_alf WHERE type = 'SERVICE' AND text LIKE '%service.' ORDER BY id ASC LIMIT 0, 10000");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
            }

            $message = ["result" => $services];
            break;
        case "actualService":
            switch ($_POST['data']) {
                case "Gendarmerie":
                    $search = $DB->prepare("SELECT MAX(s.insert_time) AS time, s.name FROM copservice AS s LEFT JOIN copservice AS s2 ON s.name = s2.name AND s2.insert_time > s.insert_time AND s2.text = 'termine son service' WHERE s2.id IS NULL AND s.text = 'prend son service' GROUP BY s.name ORDER BY time DESC;");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "Pompier":
                    $search = $DB->prepare("SELECT MAX(s.insert_time) AS time,  s.name FROM medservice AS s LEFT JOIN medservice AS s2 ON s.name = s2.name AND s2.insert_time > s.insert_time AND s2.text = 'termine son service' WHERE s2.id IS NULL AND s.text = 'prend son service' GROUP BY s.name ORDER BY time DESC;");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                default:
                    $search = $DB->prepare("SELECT MAX(s.insert_time) AS time,  s.name FROM logs_alf AS s LEFT JOIN logs_alf AS s2 ON s.name = s2.name AND s2.insert_time > s.insert_time AND s2.text = 'termine son service.' WHERE s2.id IS NULL AND s.text = 'prend son service.' GROUP BY s.name ORDER BY time DESC;");
                    $search->execute();
                    $services = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
            }

            $message = ["result" => $services];
            break;
        case "vehicules":
            switch ($_POST['data']) {
                case "Gendarmerie":
                    $search = $DB->prepare("SELECT * FROM vehicles_cop ORDER BY classname ASC");
                    $search->execute();
                    $vehicules = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case "Pompier":
                    $search = $DB->prepare("SELECT * FROM vehicles_med ORDER BY classname ASC");
                    $search->execute();
                    $vehicules = $search->fetchAll(PDO::FETCH_ASSOC);
                    break;
                default:
                    $vehicules = "Not Found";
                    break;
            }
            $message = ["result" => $vehicules];
            break;
        case "TAJ":
            switch ($_POST["action"]) {
                case "new":
                    $id = $_POST['id'];
                    $name = encode($_POST['name']);
                    $lieu = encode($_POST['PV']);
                    $date = date("d/m/Y H:i");
                    $infra = encode($_POST['motif']);
                    $DB->exec("INSERT INTO taj (id, name, lieu, date, infra, active, time, type, insert_time) VALUES ($id, '$name', '$lieu', '$date', '$infra', 1, 15, 1, '$time');");
                    $message = ["result" => "TAJ entry added."];
                    break;
                case "edit":
                    $id = $_POST['id'];
                    $name = encode($_POST['name']);
                    $lieu = encode($_POST['PV']);
                    $infra = encode($_POST['motif']);
                    $DB->exec("UPDATE taj SET name='$name', lieu='$lieu', infra='$infra' WHERE id=$id;");
                    $message = ["result" => "TAJ entry edited."];
                    break;
                    break;
                case "delete":
                    $id = $_POST['id'];
                    $DB->exec("DELETE FROM taj WHERE id=$id");
                    $message = ["result" => "TAJ entry deleted."];
                    break;
            }
            break;
        case "FPR":
            switch ($_POST["action"]) {
                case "new":
                    $id = $_POST['id'];
                    $name = encode($_POST['name']);
                    $motif = encode($_POST['motif']);
                    $DB->exec("INSERT INTO fpr (id, name, motif, active, time, insert_time) VALUES ($id, '$name', '$motif', 1, 1, '$time')");
                    $message = ["result" => "FPR entry added."];
                    break;
                case "delete":
                    $id = $_POST['id'];
                    $DB->exec("DELETE FROM fpr WHERE id=$id");
                    $message = ["result" => "FPR entry deleted."];
                    break;
                case "deleteAll":
                    $name = encode($_POST['name']);
                    $DB->exec("DELETE FROM fpr WHERE name='$name'");
                    $message = ["result" => "FPR entries deleted."];
                    break;
            }
            break;
        case "user":
            $name = encode($_POST['name']);
            $ADN = $_POST['ADN'];
            $face = $_POST['face'];
            $DB->exec("DELETE FROM gen_adn WHERE text_info='$name';");
            $DB->exec("DELETE FROM gen_face WHERE text_info='$name';");

            if ($ADN != "")
                $DB->exec("INSERT INTO gen_adn (code_adn, text_info, time) VALUES ('$ADN', '$name', 15);");
            if ($face != "")
                $DB->exec("INSERT INTO gen_face (code_face, text_info, time) VALUES ('$face', '$name', 15);");

            $message = ["result" => "ADN and Face entries updated."];
            break;
        case "SIV":
            $plaque = '%';
            for ($i = 0; $i < strlen($_POST["data"]); $i++)
                $plaque .= '`'.$_POST["data"][$i].($i == strlen($_POST["data"]) - 1 ? '' : '`,');
            $plaque .= '%';

            $search = $DB->prepare("SELECT players.name FROM vehicles JOIN players ON vehicles.pid = players.playerid WHERE vehicles.plate LIKE :plaque");
            $search->bindParam(':plaque', $plaque);
            $search->execute();
            $message = ["result" => $search->fetchAll(PDO::FETCH_ASSOC)];
            break;
        case "infos":
            $name = $_POST['data'];
            $search = $DB->prepare("SELECT * FROM players WHERE name = :name");
            $search->bindParam(':name', $name);
            $search->execute();
            if ($search->rowCount() != 1)
                $message = ["result" => "Not Found."];
            else {
                $p = $search->fetch(PDO::FETCH_ASSOC);
                $message = [
                    "result" => [
                        "name"       => $p["name"],
                        "points"     => $p["permis"],
                        "suspension" => $p["permis_time"],
                        "lastPlayed" => $p["lastPlayed"],
                    ],
                ];
            }
            break;
        default:
            $message = ["result" => "Not Found."];
            break;
    }
} catch (Exception $e) {
    $message = ["result" => "Error", "message" => $e->getMessage()];
}

header('Content-type: application/json');
echo json_encode($message);