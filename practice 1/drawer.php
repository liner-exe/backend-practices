<?php

require_once ("drawer_lib.php");

if (!isset($_GET['num'])) {
    die("<h1>Error!</h1><p>Key num is undefined</p>");
}

$code = (int)$_GET['num'];

echo buildShape((int)$_GET['num']);