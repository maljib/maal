<?php // getCount5a.php
require_once 'functions.php';

echo json_encode(selectRows('nick,count(words.id) count',
                   'words JOIN users ON user = users.id',
      "tell = 2 GROUP BY user ORDER BY count DESC, nick"));
?>
