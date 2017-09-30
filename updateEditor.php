<?php // updateEditor.php
require_once 'functions.php';

if ($nick = getPost('editor')) {
  $nick = escapeString($nick);
  $set = selectValue('id', 'users', "nick='$nick'") or die('2');
  $set = "user=$set";
  ($uid = getPost('uid')) && ($wid = getPost('wid')) or die('3');
  sqlUpdate('texts', $set, "word=$wid AND i=0 AND user=$uid") and
  sqlUpdate('words', $set, "id=$wid") and die('1');
  echo '0';
}
?>
