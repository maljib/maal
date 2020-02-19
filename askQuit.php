<?php // askQuit.php -- 탈퇴 요청: rank를 -1로 바꾼다.
require_once 'mail.php';

sqlUpdate('users', 'rank=-1', 'id='.$_POST['id']) and
sendMail4($_POST['sure'], '', '탈퇴 요청', $_POST['nick'].' 님이 탈퇴를 요청했습니다.');
?>
