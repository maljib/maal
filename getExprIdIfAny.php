<?php // getExprId.php
require_once 'functions.php';
$arg = escapeString(getPost('arg'));
echo selectValue("SELECT id FROM exprs WHERE expr = '$arg'");
?>
