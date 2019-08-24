<?php // getData.php
require_once 'functions.php';

$a = array();
if ($arg = getPost('arg')) {
  $arg  = explode(',', $arg);
  $i    = count($arg) === 2? "0 AND e.id <> $arg[1]": '1';
  $rows = selectRows(<<< SQL
SELECT u.id, u.nick, e.id, convert_tz(t,'+00:00','+09:00'), e.data
  FROM texts e JOIN users u ON u.id=user
 WHERE e.word = $arg[0] AND e.i = $i ORDER BY t DESC
SQL
  );
  foreach ($rows as $row) {
    $a[] = array('uid'=>$row[0], 'nick'=>$row[1], 'id'=>$row[2],
                   't'=>substr($row[3], 2, 14), 'data'=>$row[4]);
  }
}
echo json_encode($a);
?>
