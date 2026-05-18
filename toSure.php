<?php // toSure.php
require_once 'functions.php';

$id = getPost('id');           // 보증인 아이디
echo json_encode(selectRows(   // user_rank: 0=보증 요청, -1=탈퇴 요청
    "SELECT id, nick, user_name, user_rank FROM users WHERE sure = $id AND user_rank < 1"
));
?>