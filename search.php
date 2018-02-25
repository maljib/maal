<?php // search.php
require_once 'functions.php';

$c = 'DISTINCT w.word';     // selected columns
$j = '';                    // join
$o = 'ORDER BY w.word';     // order
$d = '';                    // 역순인가?
if ($w = getPost('l')) {    // l이 들어있는 올림말
  $w = "w.word LIKE '%".escapeString($w)."%' AND ";
} else {
  if ($w = getPost('w')) {  // w부터 또는 w까지
    if ($w{0} === '-') {    // 역순인가?
      $d = ' DESC';
      $w = substr($w, 1);
    }
    if ($w) {
      $w = 'w.word '.($d? '<': '>')."= '".escapeString($w)."' AND ";
    }
  }
  if ($x = getPost('x')) {
    $s = $x{0};              // @ # $ ^ & --> a e m 1 2
    $j = " JOIN users u ON u.nick = '".escapeString(substr($x, 1))."' AND u.id = ";
    if ($s == 'e' || $s == 'm') {
      $j = 'JOIN texts e ON w.id = e.word AND e.i='.
           ($s == 'e'? '0 AND w.user <> e.user': '1').$j.'e.user';
    } else {
      $j .= 'w.user';
      if ($s != 'a') {
        $j .= ' AND w.tell = '.$s;
      }
      if (!$w) {
        $j .= ' JOIN texts e ON w.id = e.word AND e.i=0 AND e.user = w.user';
      }
    }
    if (!$w) {
      $c = 'w.word, max(e.t) tt';
      $o = 'GROUP BY w.word ORDER BY tt';
      $d = $d? '': ' DESC';
    }
  }
}
echo json_encode(selectValues($c, "words w $j", "$w w.word<>'?' $o$d LIMIT 200"));
?>
