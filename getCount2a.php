<?php // getCount2a.php
require_once 'functions.php';

echo json_encode(selectRows('nick, count(t.id) count',
  'texts t JOIN users u ON t.user = u.id JOIN words w ON w.id = t.word',
  "t.i=0 AND t.user<>w.user AND w.word<>'?' GROUP BY t.user ORDER BY count DESC, nick"));
?>
