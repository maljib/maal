<?php // confirmMail.php
require_once 'functions1.php';
require_once 'mail.php';

$nick = getPost('nick');
$mail = getPost('mail');
if ($nick && $mail) {
  $id = getPost('id');
  if ($id && $id{0} == '-') {          // 비밀번호 변경
    sendConfirm('비밀번호를 바꾸', 'index1', substr($id, 1), $nick);
  } else if ($id && $id{0} == '@') {   // 본인 확인 후 이메일 변경
    getMailUser($mail = getPost('a-mail')) and die('16');
    $data = mess($mail)."\t".mess(getPost('phone'))."\t".getPost('askt');
    sendConfirm("이 전자우편 주소로 바꾸", 'askMail', substr($id, 1), $data);  
  } else {
    getMailUser($mail) and die('16');
    if ($id) {                         // 이메일 변경
      sendConfirm("이 전자우편 주소로 바꾸", 'updateMail', $id, mess($mail));        
    } else {
      $name = getPost('name'); 
      $sure = getPost('sure');
      setNickSure($nick, $sure, '');   // 새로 가입
      sendConfirm('가입하', 'index1', '0', "$nick\t$name\t".mess($mail)."\t$sure");
    }
  }
}

function sendConfirm($msg, $next, $id, $data) {
  global $nick, $mail;
  $data = escapeString($data);
  $i = sqlInsert('etc', 'user,data', "$id,'$data'") or die('etc 데이터 기록 에러');
  $host = $_SERVER['SERVER_NAME'];
  $text = <<<END_OF_TEXT
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
a { text-decoration:none; background-color:#efefef;
    border:1px solid #aaaaaa; border-radius:5px; padding: 0 2px; }
</style>
</head>
<body>
$nick 님께,<br><br>배달말집입니다.<br>${msg}시려면
<a href="http://$host/maal/$next.php?i=$i"><strong>확인</strong></a>을 누르십시오.
</body>
</html>
END_OF_TEXT;
  sendMail($nick, $mail, '전자우편 주소 확인', $text, false, false, false, true);
  echo $id == '0'? 'a': '0';  // 반환값: 'a'=새로 가입, '0'=새로 가입 아님
}
?>
