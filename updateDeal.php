<?php // updateDeal.php
require_once 'functions2.php';

$id  = $_POST['id'];
$dir = $_POST['dir']; 
$de  = $_POST['de'];
$al0 = $_POST['al'];
$al  = getExprId($_POST['als']);
if ($de == '0') {
    $de  = 'NULL';
    $set = '';
} else {
    $uid = $_POST['uid'];
    $set = "user=$uid,";
}
$set .= $dir == '0'?  "de=$de,al=$al": "de=$al,al=$de";
$rc = sqlUpdate('deals', $set, "id=$id");
$rc == 1 and touchMal();
echo $rc;
deleteUnusedExpr($al0)
?>
