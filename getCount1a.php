<?php // getCount1a.php
  require_once 'functions.php';

  echo json_encode(selectRows(<<< SQL
SELECT nick,count(words.id) count
  FROM words JOIN users ON user = users.id
 WHERE word <> '?' GROUP BY user ORDER BY count DESC, nick
SQL
  ));
?>
