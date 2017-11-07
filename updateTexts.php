<?php
require_once 'functions.php';

$rows = selectRows('id, data', 'texts', "data like '%☛%' or data like '%㸃%'");
foreach ($rows as $row) {
  $data = preg_replace('/☛/u', '☞', $row[1]);
  $data = escapeString(preg_replace('/㸃/u', '點', $data));
  sqlUpdate('texts', "t=t, data='$data'", 'id='.$row[0]) == 1 or die('error');
}
echo 'done';
?>
