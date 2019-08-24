<?php // addDeal.php
require_once 'functions.php';

$a = explode(',', $_POST['a']);   // deal, uid
$s = escapeString($_POST['s']);
echo sqlInsert('notes', 'data,deal,user', "'$s',$a[0],$a[1]");
?>
