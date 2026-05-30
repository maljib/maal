<?php // checkPass.php  -- 비밀번호 검사
require_once 'functions.php';

// 비밀번호의 해시 값을 검사한다 (비밀번호, 해시 값)
function checkPassOld($password, $hash) {
  return hash_pbkdf2("sha256", $password, substr($hash, 16), 1000, 16, true)
         === substr($hash, 0, 16);
}

$id   = getPost('id');
$pass = getPost('pass');
$row  = selectRow("SELECT passwd, user_rank, pass FROM users WHERE id = $id")
                    or die('3'); // 없음
$hash = $row[0];
if ($hash) {
  $ok = password_verify($pass, $hash);
} else {
  $ok = checkPassOld($pass, $row[2]);
  if ($ok) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    sqlUpdate("users", "passwd = '$hash'", "id = $id");
  }
}
echo $ok? $row[1]: "2";
?>
