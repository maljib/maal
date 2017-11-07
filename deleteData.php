<?php // deleteData.php
require_once 'functions.php';

if ($a = getPost('a')) {
  $a  = explode(',', $a);
  $rc = sqlDelete('texts', "id=$a[0]");
  if ($rc == 1 && count($a) === 2) {
    $rc = sqlDelete('words',
           "id=$a[1] AND NOT EXISTS (SELECT id FROM texts WHERE word=$a[1])");
  }
  if ($rc == 1) {
    getPost('i') == '0' and touchMaljib();
    echo '1';
  }
}
?>
