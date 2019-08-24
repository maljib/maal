<?php // updateNote.php
require_once 'functions.php';
$w = 'id='.$_POST['a'];
$s = escapeString($_POST['s']);
if ($s == '') {
  echo sqlDelete('notes', $w);
} else {
  echo sqlUpdate('notes', "data='$s'", $w);
}
?>
