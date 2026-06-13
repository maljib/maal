<?php // getHelp.php
require_once 'functions.php';
echo selectValue(<<< SQL
SELECT data
FROM texts e
JOIN words w ON e.word = w.id
WHERE w.word = '?' AND i = 0 ORDER BY t DESC LIMIT 1
SQL
);
?>
