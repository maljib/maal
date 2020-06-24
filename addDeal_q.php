<?php // addDeal_q.php
require_once 'functions2.php';

$de = getExprId(getPost('des'));
$id = sqlInsert('deals', getPost('dir') == '0'? 'de': 'al', "$de");
is_numeric($id) or die($id);
echo $de;
?>