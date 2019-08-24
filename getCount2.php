<?php // getCount2.php
  require_once 'functions.php';
  echo selectValue(<<< SQL
SELECT count(w.id)
  FROM words w JOIN texts t ON w.id = t.word AND w.user <> t.user
 WHERE t.i = 0 AND w.word <> '?'
SQL
  );
?>
