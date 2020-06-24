<?php // deleteDeal.php
require_once 'functions2.php';

$id = $_POST['id'];
$rc = sqlDelete('deals', 'id='.$id);
$aa = explode(',', $_POST['a']);
foreach ($aa as $a) {
  deleteUnusedExpr($a);
}
$rc == 1 and touchMal();
echo $rc;
?>
