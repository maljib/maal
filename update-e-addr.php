<?php // update-e-addr.php
require_once 'functions.php';

$i = getGet('i');
$e = getGet('e');
if ($i && $e) {
  $mail = escapeString(mess($e));
  sqlUpdate('users', "mail='$mail'", "id=$i") or die('Update error.');
  echo "전자우편 주소가 ".$e."으로 바뀌었습니다.";
}
?>
