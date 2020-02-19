<?php // addDeal.php
require_once 'functions2.php';

$cols = (getPost('dir') == '0'? 'de,al': 'al,de').',user';
$de   = getPost('des');
$de   = $de? getExprId($de): getPost('dei');
$al   = getExprId($_POST['als']);
$uid  = getPost('uid');
$id   = sqlInsert('deals', $cols, "$de,$al,$uid");
is_numeric($id) or die($id);
echo $de;
?>
