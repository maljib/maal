<?php // updatePass.php
require_once 'functions.php';

$id   = getPost('id');
$pass = getPost('pass');
if ($id && $pass) {
  $hash  = escapeString(password_hash($pass, PASSWORD_DEFAULT));
  $count = sqlUpdate('users', "passwd='$hash'", "id=$id");
  if ($count == 1) deleteEtc(getPost('i'));
  echo $count;
}
?>
