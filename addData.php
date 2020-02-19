<?php // addData.php
require_once 'functions.php';

if (isset($_POST['a']) && isset($_POST['data'])) {
  $a    = explode(',', $_POST['a']);  // 0=i 1=word 2=user
  $data = escapeString($_POST['data']);
  $cols = $a[2]?          'i,word,user,data': 'i,word,data';
  $vals = $a[2]? "$a[0],$a[1],$a[2],'$data'": "$a[0],$a[1],'$data'";
  if (is_numeric(sqlInsert('texts', $cols, $vals))) {
    $a[0] == '0' and touchMaljib();
    echo '1';  
  }
}
?>
