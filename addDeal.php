<?php // addDeal.php
require_once 'functions2.php';
$a = explode(',', $_POST['a']);   // rv, de, uid
is_numeric($al = getExprId($_POST['s'])) or die($al);
$cols = ($a[0] == '0'? 'de,al': 'al,de').',user';
echo sqlInsert('deals', $cols, "$a[1],$al,$a[2]");
?>
