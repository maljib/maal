<?php // updatePass.php
  require_once 'functions.php';

  $id   = getPost('id');
  $pass = getPost('pass');
  if ($id && $pass) {
    $pass  = escapeString(getHash($pass));
    $count = sqlUpdate('users', "pass='$pass'", "id=$id");
    if ($count == 1) deleteEtc(getPost('i'));
    echo $count;
  }
?>
