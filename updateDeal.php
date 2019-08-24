<?php // updateDeal.php
require_once 'functions2.php';

$a = explode(',', $_POST['a']);   // id, rv
$al = getExprId($_POST['s']);
count($a) == 3 && is_numeric($al) or die($al);
$set = $a[1] == '0'? "de=$a[2],al=$al": "al=$a[2],de=$al";
echo sqlUpdate('deals', $set, "id=$a[0]");
?>
