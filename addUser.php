<?php // addUser.php
require_once 'mail.php';
if (isset($_POST['nick']) && isset($_POST['pass']) &&
    isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['sure'])) {
  $nk   = escapeString($nick = $_POST['nick']);
  $sure = escapeString($_POST['sure']);
  selectValue("SELECT id FROM users WHERE nick = '$nk'") and die('4');
  $row  = selectRow("SELECT id, rank FROM users WHERE nick = '$sure'");
  $row && 0 < $row[1] or die('8');
  $sid  = $row[0];
  $ps   = escapeString(getHash($_POST['pass']));
  $nm   = escapeString($name = $_POST['name']);
  $ml   = escapeString(mess($_POST['mail']));
  sqlInsert('users','nick,pass,name,mail,sure',"'$nk','$ps','$nm','$ml',$sid");
  deleteEtc(getPost('i'));
  $nick === $name or $nick .= '('.$name.')';
  sendMail3($sid, '보증 요청', $nick.' 님이 보증을 요청했습니다.');
  echo '2';
}
?>
