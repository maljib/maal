<?php // updateTell.php
require_once 'functions.php';

$tell = getPost('tell');
if (sqlUpdate('words', 'tell='.$tell, 'id='.$_POST['wid']) == 1) {
  $tell < '2' and touchMaljib();
  echo '1';
}
?>
