<?php // searchWord.php
require_once 'functions.php';

$term = getGet('term') or die('[]');
$term = escapeString($term);
echo json_encode(selectValues("SELECT word FROM words WHERE word like '$term%'"));
?>
