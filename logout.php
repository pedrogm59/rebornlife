<?php
session_start();
$_SESSION['Auth'] = array();
session_destroy();
header('Location: /');
