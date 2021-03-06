<?php // getData_e.php
require_once 'functions.php';

if ($de = $_POST['a']) {
  $rows = selectRows(<<< SQL
SELECT d.id, 0 dir, date_format(convert_tz(d.c,'+00:00','+09:00'), '%y-%m-%d %H:%i') c,
       d.al, f.expr, u.id, u.nick
  FROM deals d JOIN exprs e ON e.id = d.de
               JOIN exprs f ON f.id = d.al
               JOIN users u ON u.id = d.user
 WHERE e.id = $de
 UNION
SELECT d.id, 1 dir, date_format(convert_tz(d.c,'+00:00','+09:00'), '%y-%m-%d %H:%i') c,
       d.de, f.expr, u.id, u.nick
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
    $rows[$i][7] = array(selectValues(
      "SELECT user from up   where deal = $id ORDER BY user"
    ), selectValues(
      "SELECT user from down where deal = $id ORDER BY user"
    ));
    $rows[$i][8] = selectRows(<<< SQL
SELECT n.id, n.data, u.id, u.nick,
       date_format(convert_tz(n.t,'+00:00','+09:00'), '%y-%m-%d %H:%i') t
  FROM notes n JOIN deals d ON d.id = n.deal
               JOIN users u ON u.id = n.user
 WHERE d.id = $id ORDER BY t DESC
SQL
    );
  }
  $n and die(json_encode($rows));
  die(json_encode(selectRows(<<< SQL
SELECT id, 0, date_format(convert_tz(t,'+00:00','+09:00'), '%y-%m-%d %H:%i'), 0
  FROM deals
 WHERE de = $de AND al IS NULL
 UNION
SELECT id, 1, date_format(convert_tz(t,'+00:00','+09:00'), '%y-%m-%d %H:%i'), 0
  FROM deals
 WHERE al = $de AND de IS NULL
SQL
  )));
}
echo '[]';
?>
