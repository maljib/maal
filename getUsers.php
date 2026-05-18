<?php // getUsers.php
  require_once 'functions.php';
  echo json_encode(selectRows(<<< SQL
SELECT u.nick, u.user_name, IFNULL(s.nick,u.nick), u.id
  FROM users u LEFT JOIN users s ON s.id = u.sure
 WHERE u.user_rank = 1 ORDER BY u.user_name, u.nick
SQL
  ));
?>
