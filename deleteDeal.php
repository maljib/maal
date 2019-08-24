<?php // deleteDeal.php
require_once 'functions.php';
echo sqlDelete('deals', 'id='.getPost('a'));
?>
