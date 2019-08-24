<?php // getCount4a.php
require_once 'functions.php';

echo json_encode(selectRows(<<< SQL
SELECT nick, count(words.id) count
  FROM words JOIN users ON user = users.id
 WHERE tell = 1 GROUP BY user ORDER BY count DESC, nick
SQL
));
?>
