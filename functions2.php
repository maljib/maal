<?php // functions2.php
require_once 'functions.php';

function getExprId($s) {
  $s  = escapeString($s);
  $id = selectValue("SELECT id FROM exprs WHERE expr = '$s'");
  return $id? $id: sqlInsert('exprs', 'expr', "'$s'");
}

function deleteUnusedExpr($a) {
  if ($a != '0') {
    sqlDelete('exprs', "id=$a AND NOT EXISTS (SELECT id FROM deals WHERE $a in (de, al))");
  }
}
?>
