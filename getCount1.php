<?php // getCount1.php
require_once 'functions.php';
echo selectValue('SELECT count(*) FROM words') - 1;
?>
