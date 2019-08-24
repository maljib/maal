<?php // functions2.php
require_once 'functions.php';

function getExprId($s) {
  $s  = escapeString($s);
  $id = selectValue("SELECT id FROM exprs WHERE expr = '$s'");
  return $id? $id: sqlInsert('exprs', 'expr', "'$s'");
}
?>
