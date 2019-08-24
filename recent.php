<?php // recent.php
  require_once 'functions.php';
  echo json_encode(selectValues(<<< SQL
SELECT distinct w.word
  FROM words w JOIN texts e ON w.id = e.word
 WHERE w.word <> '?' ORDER BY e.t DESC LIMIT 200
SQL
  ));
?>
