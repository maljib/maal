<?php // getUsers.php
  require_once 'functions.php';
  echo json_encode(selectRows(<<< SQL
SELECT u.nick, u.name, IFNULL(s.nick,u.nick), u.id
  FROM users u LEFT JOIN users s ON s.id = u.sure
 WHERE u.rank = 1 ORDER BY u.name, u.nick
SQL
  ));
?>
