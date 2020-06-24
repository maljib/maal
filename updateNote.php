<?php // updateNote.php
require_once 'functions.php';

$data  = escapeString($_POST['data']);
$where = 'id='.$_POST['id'];
if ($data == '') {
  $rc = sqlDelete('notes', $where);
} else {
  $rc = sqlUpdate('notes', "data='$data'", $where);
}
$rc == 1 and touchMal();
echo $rc;
?>
