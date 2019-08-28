<?php // getData_e.php
require_once 'functions.php';

if ($de = $_POST['a']) {
  $rows = selectRows(<<< SQL
SELECT d.id, 0 dir, f.expr, d.vote, u.id, u.nick,
       date_format(convert_tz(d.t,'+00:00','+09:00'), '%Y-%m-%d %H:%i'), d.c
  FROM deals d JOIN exprs e ON e.id = d.de
               JOIN exprs f ON f.id = d.al
               JOIN users u ON u.id = d.user
 WHERE e.id = $de
 UNION
SELECT d.id, 1 dir, f.expr, d.vote, u.id, u.nick,
       date_format(convert_tz(d.t,'+00:00','+09:00'), '%Y-%m-%d %H:%i'), d.c
  FROM deals d JOIN exprs e ON e.id = d.al
               JOIN exprs f ON f.id = d.de
               JOIN users u ON u.id = d.user
 WHERE e.id = $de AND d.al <> d.de
 ORDER BY dir, c;
SQL
  );
  $n = count($rows);
  for ($i = 0; $i < $n; $i++) {
    $id = $rows[$i][0];
    $rows[$i][7] = selectRows(<<< SQL
SELECT n.id, n.data, u.id, u.nick, 
       date_format(convert_tz(n.t,'+00:00','+09:00'), '%Y-%m-%d %H:%i') t, n.c
  FROM notes n JOIN deals d ON d.id = n.deal
               JOIN users u ON u.id = n.user
 WHERE d.id = $id ORDER BY c
SQL
    );
  }
  $n and die(json_encode($rows));
}
echo '[]';
?>
