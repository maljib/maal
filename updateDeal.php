<?php // updateDeal.php
require_once 'functions2.php';

$a  = explode(',', $_POST['a']);  // [0]=id, [1]=0/1, [2]=de/al
$al = getExprId($_POST['s']);     // al
is_numeric($al) or die($al);

$set = $a[1] == '0'? "de=$a[2],al=$al":
                     "al=$a[2],de=$al";
echo sqlUpdate('deals', $set, "id=$a[0]");
?>
