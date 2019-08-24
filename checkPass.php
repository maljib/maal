<?php // checkPass.php  -- 비밀번호 검사
require_once 'functions.php';
$row = selectRow('SELECT pass, rank FROM users WHERE id = '.getPost('id'))
          or die('3'); // 없음
echo checkPass(getPost('pass'), $row[0])? $row[1]: "2";  // 맞음: 틀림
?>
