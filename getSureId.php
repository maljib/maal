<?php // getSureId.php
require_once 'functions.php';
$sure = escapeString($_POST['sure']);
$row  = selectRow("SELECT id, rank FROM users WHERE nick = '$sure'");
echo $row && 0 < $row[1]? $row[0]: '0';
?>
