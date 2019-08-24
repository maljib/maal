<?php // toSure.php
require_once 'functions.php';
echo json_encode(selectRows(
'SELECT id, nick, name, rank FROM users WHERE sure = '.
                           $_POST['id'].' AND rank < 1'));
?>
