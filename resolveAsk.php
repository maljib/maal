<?php // resolveAsk.php
require_once 'mail.php';

if ($a = getPost('a')) {
  $a = explode('_', $a); // 0=(0=o 1=x) 1=askId 2=userId
  $mail = mess($m = selectValue('mail', 'asks', 'id='.$a[1]));
  $nick = selectValue('nick', 'users', 'id='.$a[2]);   // 아이디,이름
  if ($a[0] == '0') {  // 0=o 1=x
    sqlUpdate('users', "mail='".escapeString($m)."'", 'id='.$a[2])
      or die('전자우편 주소 업데이트 실패');
    sendMail4($nick, $mail, '전자우편 주소변경 승인', '전자우편 주소가 '.$mail.'으로 바뀌었습니다.');
  } else {
    sendMail4($nick, $mail, '전자우편 주소변경 거절', "전자우편 주소가 바뀌지 않았습니다.");
  }
  sqlDelete('asks', 'id='.$a[1]) or die('전자우편 주소 변경 요청 정보 지우기 실패.');
}
?>
