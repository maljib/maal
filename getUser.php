<?php // getUser.php
require_once 'functions.php';

$nick = getPost('nick');
$column = 'nick';    // 아이디로 찾기
if (preg_match('/@\w+\./', $nick)) {
  $nick = mess($nick);
  $column = 'mail';  // 전자우편 주소로 찾기
}
$nick = escapeString($nick);
$row  = selectRow(<<< SQL
SELECT u.id, u.name, u.mail, u.rank, s.nick, u.nick
  FROM users u LEFT OUTER JOIN users s ON u.sure = s.id
 WHERE u.$column = '$nick'
SQL
) or die($column == 'mail'? '{"id":"0"}': '{}');
if ($row[3] < 0) {
  sqlUpdate('users', 'rank=1', "id=$row[0]");
  $row[3] = '1';
}
echo json_encode(array('id'=>$row[0],'name'=>$row[1],'mail'=>mess($row[2]),
                       'rank'=>$row[3],'sure'=>($row[4]? $row[4]: $row[5]),
                       'nick'=>$row[5]));
?>
