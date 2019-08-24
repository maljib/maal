<?php // search.php
require_once 'functions.php';

$j = '';                    // join clauses
$o = 'ORDER BY e.expr';     // ordering instruction
$d = '';                    // ' DESC' = 역순
if ($w = getPost('v')) {    // 이 들어있는 올림말
  $w = "WHERE e.expr LIKE '%".escapeString($w)."%'"; 
} else {
  if ($w = getPost('w')) {  // where clause
    if ($w{0} === '-') {
      $d = ' DESC';         // 역순
      $w = substr($w,1);
    }
    if ($w) {               // $w부터 가나다 순 또는 $w부터 가나다 역순
      $w = 'WHERE e.expr '.($d? '<': '>')."= '".escapeString($w)."'";
    }
  }
  if ($x = getPost('x')) {
    $j = $x{0} == 'a'? 'JOIN users u ON u.id = d.user':
                       'JOIN notes n ON d.id = n.deal'.
                      ' JOIN users u ON u.id = n.user';
    $u = "u.nick = '".escapeString(substr($x,1))."'";
    if ($w) {    // $w가 없으면 맨 나중에 / 맨 처음에 넣은 것 부터
      $w .= ' AND '.$u;
    } else {
      $w = 'WHERE '.$u;
      $o = 'ORDER BY d.c';
      $d = $d? '': ' DESC';
    }
  }
}
echo json_encode(selectRows(<<< SQL
SELECT DISTINCT e.id, e.expr
  FROM exprs e JOIN deals d ON e.id IN (d.de, d.al) $j
    $w $o$d LIMIT 200
SQL
));
?>
