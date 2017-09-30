<?php // updateData.php
require_once 'functions.php';

if ($id = getPost('id')) {
  $set = '';
  if ($user = getPost('user')) {
    $set = $user == '-'? "t=now()": "user=$user";
  } else if ($data = getPost('data')) {
    $set = "data='".escapeString($data)."'";
  }
  $set and (print sqlUpdate('texts', $set, "id=$id"));
}
?>
