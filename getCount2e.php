<?php // getCount2e.php
  require_once 'functions.php';
  echo selectValue(<<< SQL
SELECT count(DISTINCT d.id)
  FROM deals d JOIN notes n ON d.id = n.deal
-- WHERE d.user <> n.user
SQL
  );
?>
