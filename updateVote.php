<?php // updateVote.php
require_once 'functions.php';

$deal  = $_POST['deal'];
$user  = $_POST['user'];
$i0    = $_POST['i0'];                       // 0=insert 1=delete
$table = $_POST['u0'] == $i0? 'up': 'down';  // 0=up     1=down
if ($i0 == '0') {
    $rc = sqlInsert($table, 'deal,user', "$deal,$user");
    echo is_numeric($rc)? '1': $rc;
} else {
    echo sqlDelete($table, "deal=$deal AND user=$user");
}
?>
