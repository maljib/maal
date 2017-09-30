<?php // updateTell.php
require_once 'functions.php';
echo sqlUpdate('words', 'tell='.$_POST['tell'], 'id='.$_POST['wid']);
?>
