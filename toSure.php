<?php // toSure.php
require_once 'functions.php';
echo json_encode(selectRows('id,nick,name,rank', 'users',
                   'sure='.$_POST['id'].' AND rank < 1'));
?>
