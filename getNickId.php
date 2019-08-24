<?php // getNickId.php
require_once 'functions.php';
$nick = escapeString($_POST['nick']);
$id   = selectValue("SELECT id FROM users WHERE nick = '$nick'");
echo $id? $id: '0';
?>
