<?php // updateMail.php
require_once 'functions1.php';

$i = getGet('i');      // etc number
$row = selectEtc($i);  // [user number, data]
if (count($row) === 2) {
  $mail = escapeString($row[1]);  // data = email address
  sqlUpdate('users', "mail='$mail'", "id=$row[0]") or die('Update error.');
  echo "전자우편 주소가 ".mess($row[1])."으로 바뀌었습니다.";
  deleteEtc($i);
}
?>
