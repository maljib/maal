<?php // isEditor.php
require_once 'functions.php';
echo selectValue('Select 1 FROM editor WHERE user = '.$_POST['id']);
?>
