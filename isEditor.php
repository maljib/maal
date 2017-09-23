<?php // isEditor.php
  require_once 'functions.php';
  echo selectValue('1', 'editor', 'user='.$_POST['id']);
?>
