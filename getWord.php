<?php // getWord.php
require_once 'functions.php';

$arg  = escapeString($_POST['arg']);
$user = $arg == '?'? '':
  ' AND (w.user=e.user OR w.tell=1 AND e.user in (select user from editor))';
$row = selectRow("u.id,nick,e.id, convert_tz(t,'+00:00','+09:00'), data,w.id,tell",
                 'words w JOIN texts e ON w.id=e.word'.$user.
                        ' JOIN users u ON u.id=e.user',
                    "w.word='$arg' AND i=0 ORDER BY e.id DESC LIMIT 1");
echo json_encode(array('uid'=>$row[0], 'nick'=>$row[1], 'id'=>$row[2],
                       't'=>substr($row[3],2,14),
                       'data'=>$row[4], 'wid'=>$row[5], 'tell'=>$row[6]));
?>
