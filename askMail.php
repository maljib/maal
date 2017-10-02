<?php // askMail.php
require_once 'mail.php';

$i = getGet('i');      // etc number
$row = selectEtc($i);  // [user number, data]
$id = $row[0];
$ur = selectRow('nick,name', 'users', "id=$id")
  or die("사용자 번호($id)가 없습니다.");

$mr = selectRow('nick,mail', 'users u JOIN master m ON u.id = m.user', '1')
  or die("관리자가 없습니다.");

$a = explode("\t", $row[1]);
$am = escapeString($a[0]);  // 전자우편 주소
$ph = escapeString($a[1]);  // 전화번호
$at = escapeString($a[2]);  // 알리는 말
sqlInsert('asks', 'user,mail,phone,askt', "$id,'$am','$ph','$at'")
  or die('전자우편 주소 변경요청이 등록되지 않았습니다.');

$body = $ur[0].($ur[0] === $ur[1]? '': '('.$ur[1].')')
              .' 님의 전자우편 주소를 '.mess($a[0])."으로 바꾸어 주십시오.\n\n";
$a[1] and $body .= '전화번호: '.mess($a[1])."\n";
$body .= $a[2];
sendMail($mr[0], mess($mr[1]), "전자우편 주소변경 요청", $body, $ur[0], mess($a[0]));
echo "전자우편 주소변경 요청을 관리자에게 보냈습니다.";
deleteEtc($i);
?>
