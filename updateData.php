<?php // updateData.php
require_once 'functions.php';

if ($id = getPost('id')) {
  $set = '';
  if ($user = getPost('user')) {
    $set = $user == '-'? "t=now()": "user=$user";
  } else if ($data = getPost('data')) {
    $set = "data='".escapeString($data)."'";
  }
  if ($set && is_numeric(sqlUpdate('texts', $set, "id=$id"))) {
    getPost('i') == '0' and touchMaljib();
    echo '1';
  }
}
?>
