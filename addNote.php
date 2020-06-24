<?php // addNote.php
require_once 'functions.php';

$data = escapeString($_POST['data']);
$deal = $_POST['deal'];
$user = $_POST['user'];
$rc = sqlInsert('notes', 'data,deal,user', "'$data',$deal,$user");
if (is_numeric($rc)) {
  touchMal();
} else {
  echo $rc;
}
?>
