<?php // getCount2ea.php
  require_once 'functions.php';

  echo json_encode(selectRows(<<< SQL
SELECT u.nick, count(n.deal) count
  FROM notes n JOIN users u ON u.id = n.user
               JOIN deals d ON d.id = n.deal
 WHERE d.user <> n.user
 GROUP BY d.user ORDER BY count DESC, u.nick
SQL
  ));
?>
