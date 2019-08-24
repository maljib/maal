<?php // recent_e.php
  require_once 'functions.php';
  echo json_encode(selectRows(<<< SQL
SELECT DISTINCT e.id, e.expr
  FROM exprs e JOIN deals d ON e.id IN (d.de, d.al)
 ORDER BY d.c DESC LIMIT 200
SQL
  ));
?>
