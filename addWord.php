<?php // addWord.php
require_once 'functions.php';

if (isset($_POST['uid']) && isset($_POST['arg']) && isset($_POST['data'])) {
  $user = $_POST['uid'];
  $word = escapeString($_POST['arg']);
  $data = escapeString($_POST['data']);
  $id   = sqlInsert('words', 'user,word', "$user,'$word'");
  if (is_numeric($id) && 0 < $id) {
    $id = sqlInsert('texts', 'i,user,word,data', "0,$user,$id,'$data'");
    if (is_numeric($id) && 0 < $id) {
      touchMaljib();
      echo '1';
    }
  }
}
?>
