<?php // updateNote.php
require_once 'functions.php';

$data  = escapeString($_POST['data']);
$where = 'id='.$_POST['id'];
if ($data == '') {
  echo sqlDelete('notes', $where);
} else {
  echo sqlUpdate('notes', "data='$data'", $where);
}
?>
