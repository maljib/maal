<?php // updateUser.php
  require_once 'functions1.php';
  require_once 'mail.php';

  $id  = getPost('id');
  $set = setNickSure(getPost('nick'), getPost('sure'), $id);
  if (isset($_POST['name'])) {
    $set = cat($set, "name='".escapeString($_POST['name'])."'");
  }
  if ($set) {
    sqlUpdate('users', $set, "id=$id") or die('Update error.');
  }
  if ($set && strpos($set, ',rank=0')) {
    $row = selectRow('sure,nick,name', 'users', "id=$id");
    $who = $row[1] === $row[2]? $row[1]: $row[1].'('.$row[2].')';
    sendMail3($row[0], '보증 요청', $who.' 님이 보증을 요청했습니다.');
    die('2');  // 보증인 변경이 있음
  }
  echo '1';    // 보증인 변경이 없음
?>
