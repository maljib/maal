<?php // deleteUser.php
require_once 'mail.php';
require_once 'functions1.php';

$a    = getPost('id');
$nick = getPost('nick');
if ($nick) {
  $a = explode(',', $a);
  if (count($a) === 2) {
    sqlUpdate('words', "user=$a[1]",     "user=$a[0]");
    sqlUpdate('texts', "user=$a[1],t=t", "user=$a[0]");
    sqlUpdate('deals', "user=$a[1],t=t", "user=$a[0]");
    sqlUpdate('notes', "user=$a[1],t=t", "user=$a[0]");
    sqlUpdate( 'asks', "user=$a[1],t=t", "user=$a[0]");
    sendMail3($a[0], "탈퇴 승낙", $_POST['nick']." 님이 탈퇴를 승낙했습니다.");
    echo sqlDelete('users', "id=$a[0]");
  } else {
    echo sqlUpdate('users', 'rank=1', "id=$a[0]");
    sendMail3($a[0], "탈퇴 거절", $_POST['nick']." 님이 탈퇴를 거절했습니다.");
  }
} else if (hasWork($a)) {
  echo '2';  // 내 글이 있으면 보증인의 승락이 있어야 탈퇴할 수 있다
} else {
  echo sqlDelete('users', "id=$a");  // 내 글이 없으면 즉시 탈퇴 처리 -- 정상이면 '1'
}
?>
