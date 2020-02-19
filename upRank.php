<?php // upRank.php
require_once 'mail.php';

$a    = getPost('id');
$nick = getPost('nick');
if ($a && $nick) {
  $a = explode(',', $a);
  if (count($a) === 2) {
    echo sqlUpdate('users', 'rank=1', "id=$a[0]");
    sendMail3($a[0], '보증 승낙', $nick.' 님이 보증했습니다.');
  } else {
    sendMail3($a[0], '보증 거절', $nick.' 님이 보증을 거절했습니다.');  
    echo sqlDelete('users', "id=$a[0]");
  }
}
?>