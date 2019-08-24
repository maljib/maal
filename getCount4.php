<?php // getCount4.php
require_once 'functions.php';
echo selectValue('SELECT count(*) FROM words WHERE tell = 1');
?>
