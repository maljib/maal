<?php // search.php
require_once 'functions.php';

$s = 'DISTINCT w.word';     // selected columns
$j = '';                    // join clauses
$o = 'ORDER BY w.word';     // ordering instruction
$d = '';                    // ' DESC' = 역순
if ($w = getPost('v')) {    // where clause
  $w = "w.word LIKE '%".escapeString($w)."%' AND";  // l이 들어있는 올림말
} else {
  if ($w = getPost('w')) {  // where clause
    if ($w{0} === '-') {
      $d = ' DESC';         // 역순
      $w = substr($w,1);
    }
    if ($w) {               // $w부터 가나다 순 또는 $w부터 가나다 역순
      $w = 'w.word '.($d? '<': '>')."= '".escapeString($w)."' AND";
    }
  }
  if ($x = getPost('x')) {
    $j = "JOIN users u ON u.nick='".escapeString(substr($x,1))."' AND u.id=";
    $x = $x{0};   // @ # $ ^ & --> a e m 1 2
    if ($x == 'm' || $x == 'e') {  // 적바림 또는 다른 사람이 적은 자취
      $j = 'JOIN texts e ON w.id=e.word AND e.i='.
           ($x == 'm'? '1': '0 AND w.user<>e.user')." $j e.user";
    } else {
      $j .= 'w.user';              // 올림말 올린이
      if ($x != 'a') {
        $j .= " AND w.tell=$x";    // $x: 1=올림 2=버림
      }
      if (!$w) {  // $w가 없으면 맨 나중에 / 처음에 넣은 것 부터
        $j .= ' JOIN texts e ON w.id=e.word AND e.i=0 AND e.user=w.user';
      }
    }
    if (!$w) {    // $w가 없으면 맨 나중에 / 맨 처음에 넣은 것 부터
      $s = 'w.word, max(e.t) max_t';
      $o = 'GROUP BY w.word ORDER BY max_t';
      $d = $d? '': ' DESC';
    }
  }
}
echo json_encode(selectValues(<<< SQL
SELECT $s
  FROM words w $j
 WHERE $w w.word<>'?' $o$d LIMIT 200
SQL
));
?>
