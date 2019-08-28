<?php // getExprIfAny.php
require_once 'functions.php';
$s = escapeString(getPost('s'));
echo selectValue("SELECT id FROM exprs WHERE expr = '$s'");
?>
