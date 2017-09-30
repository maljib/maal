<?php // searchWord.php
require_once 'functions.php';

$term = getGet('term') or die('[]');
$term = escapeString($term);
echo json_encode(selectValues('word', 'words', "word like '$term%'"));
?>
