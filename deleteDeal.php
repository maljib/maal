<?php // deleteDeal.php
require_once 'functions.php';
$id = getPost('a');
$da = selectRow('SELECT de, al FROM deals WHERE id = '.$id);
$rc = sqlDelete('deals', 'id='.$id);
foreach ($da as $a) {
  sqlDelete('exprs', "id=$a AND NOT EXISTS (SELECT id FROM deals WHERE $a in (de, al))");
}
echo $rc;
?>
