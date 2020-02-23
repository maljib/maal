<?php // getData_q.php
require_once 'functions.php';

echo json_encode(selectRows(<<< SQL
SELECT d.id, 0 dir, date_format(convert_tz(d.t,'+00:00','+09:00'), '%y-%m-%d %H:%i'), d.al, f.expr, d.c
  FROM deals d JOIN exprs f ON f.id = d.al
 WHERE d.de is NULL
 UNION
SELECT d.id, 1 dir, date_format(convert_tz(d.t,'+00:00','+09:00'), '%y-%m-%d %H:%i'), d.de, f.expr, d.c
  FROM deals d JOIN exprs f ON f.id = d.de
 WHERE d.al is NULL
 ORDER BY dir, c;
SQL
));
?>