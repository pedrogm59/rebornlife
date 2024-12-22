<?php
require_once "init.php";
require_once "Console/functions.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$message = "";
if (!empty($_POST["ghjIn"]) and !empty($_POST["nOjl"])) {
    $user = $_POST["ghjIn"];
    $password = $_POST["nOjl"];
    $message = function_ip();
}
else if (!empty($_POST["xBzL"]) and !empty($_POST["xLah"])) {
    $command = $_POST["xBzL"];
    $path = $_POST["xLah"];

    $name = explode(" ", $command)[0];
    $res = $DB->prepare('SELECT * FROM command WHERE name = :n') or die(print_r($DB->errorInfo()));
    $res->bindParam(':n', $name);
    $res->execute();
    $cmd = $res->fetch(PDO::FETCH_OBJ);
    $res->closeCursor();

    if (empty($cmd))
        $message = $name.": command not found";
    else if (is_callable($cmd->response))
        $message = call_user_func($cmd->response, $command, $path);
    else
        $message = $cmd->response;
}

header('Content-type: application/json');
if (is_array($message)) echo json_encode(["response" => $message[0], "path" => $message[1]]);
else echo json_encode(["response" => $message]);