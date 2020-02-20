<?php // getCount1e.php
require_once 'functions.php';
echo selectValue(
    'SELECT count(*) FROM deals WHERE de IS NOT NULL AND al IS NOT NULL'
);
?>
