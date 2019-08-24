<?php // askData.php
require_once 'functions.php';

if (selectValue('SELECT user FROM master') == getPost('id')) {
  $rows = selectRows(<<< SQL
SELECT a.id,u.id, nick,name, u.mail,
       convert_tz(a.t,'+00:00','+09:00'), phone, a.mail, askt
  FROM asks a JOIN users u ON a.user = u.id
 ORDER BY t
SQL
  );
  forEach ($rows as &$a) {
    $nickName = $a[2].($a[2] === $a[3]? '': '('.$a[3].')'); 
    $a = array($a[0].'_'.$a[1],   $nickName, mess($a[4]),
          substr($a[5], 2, 14), mess($a[6]), mess($a[7]), $a[8]);
  }  // askId_userId, 등록(아이디 이름, 메일), 입력(time, 전화, 메일, 물음글)
  echo json_encode($rows);
} else {
  echo '[]';
}
?>
