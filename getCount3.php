<?php // getCount3.php  -- 적바림 수
require_once 'functions.php';
echo selectValue(<<< SQL
SELECT count(DISTINCT w.id)
FROM words w
JOIN texts t ON w.id = t.word
WHERE t.i = 1 AND w.word <> '?'
SQL
);
?>
