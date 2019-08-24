<?php // updateVote.php
require_once 'functions.php';
$a = $_POST['a'];
$s = escapeString($_POST['s']);
echo sqlUpdate('deals', "vote='$s'", "id=$a");
?>
