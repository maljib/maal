<?php // sendMail.php
require_once 'mail.php';

if (isset($_POST['ids']) && isset($_POST['subj']) && isset($_POST['note'])) {
  $to  = array();
  $toa = array();
  $rows = selectRows('nick,mail', 'users', 'id in ('.$_POST['ids'].')');
  foreach ($rows as $row) {
    $to[]  = $row[0];
    $toa[] = mess($row[1]);
  }
  if (count($to) < 1) die('메일 주소가 없습니다.');
  sendMail($to, $toa, $_POST['subj'], $_POST['note'], $_POST['re'], $_POST['rea'], $_POST['atts']);
}
?>
