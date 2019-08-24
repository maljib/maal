<?php // getCount1ea.php
  require_once 'functions.php';

  echo json_encode(selectRows(<<< SQL
SELECT u.nick, count(d.id) count
  FROM deals d JOIN users u ON u.id = d.user
 GROUP BY d.user ORDER BY count DESC, u.nick
SQL
  ));
?>
