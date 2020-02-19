<?php // toSure.php
require_once 'functions.php';

$id = getPost('id');           // 보증인 아이디
echo json_encode(selectRows(   // rank: 0=보증 요청, -1=탈퇴 요청
    "SELECT id, nick, name, rank FROM users WHERE sure = $id AND rank < 1"
));
?>