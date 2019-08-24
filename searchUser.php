<?php // searchUser.php
require_once 'functions.php';

$term = getGet('term') or die('[]');
$term = escapeString($term);
echo json_encode(selectValues("SELECT nick FROM users WHERE nick like '%$term%'"));
?>
