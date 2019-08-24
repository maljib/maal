<?php // getCount3a.php
require_once 'functions.php';

echo json_encode(selectRows(<<< SQL
SELECT nick, count(DISTINCT w.id) count
  FROM texts t JOIN users u ON t.user = u.id
               JOIN words w ON w.id = t.word
 WHERE t.i = 1 AND w.word <> '?'
 GROUP BY t.user ORDER BY count DESC, nick
SQL
));
?>
