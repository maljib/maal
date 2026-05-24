<?php // recent_e.php
  require_once 'functions.php';
  echo json_encode(selectRows(<<< SQL
SELECT e.id, e.expr
  FROM exprs e JOIN deals d ON e.id IN (d.de, d.al)
 GROUP BY e.id, e.expr ORDER BY MAX(d.c) DESC LIMIT 200
SQL
  ));
?>
