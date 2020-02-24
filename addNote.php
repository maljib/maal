<?php // addDeal.php
require_once 'functions.php';

$data = escapeString($_POST['data']);
$deal = $_POST['deal'];
$user = $_POST['user'];
echo sqlInsert('notes', 'data,deal,user', "'$data',$deal,$user");
?>
