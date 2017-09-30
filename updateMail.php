<?php // updateMail.php
require_once 'functions.php';

$i   = getGet('i');    // etc number
$row = selectEtc($i);  // [user number, data]
if (count($row) === 2) {
  $mail = escapeString(mess($row[1]));  // data = email address
  sqlUpdate('users', "mail='$mail'", "id=$row[0]") or die('Update error.');
  deleteEtc($i);
  echo "전자우편 주소가 ".$row[1]."으로 바뀌었습니다.";
}  
?>
