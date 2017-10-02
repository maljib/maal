<?php
require_once 'functions.php';

$i    = getGet('i');                 // etc number
$row  = selectEtc($i);               // [user number, data]
$id   = $row[0] == '0'? '': $row[0]; // user number
$data = explode("\t", $row[1]);      // data
$nick = $data[0];
$name = $id? '': $data[1];
$mail = $id? '': mess($data[2]);
$sure = $id? '': $data[3];
echo <<<END_OF_TEXT
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <title>배달말집</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css">
  <link rel="stylesheet" href="index1.css">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script src="index1.js"></script>
</head>
<body>
  <span id="h1">배달말집</span>
  <div id="tabs">
    <div id="user">
      <div id="title">$nick 님의 비밀번호</div>
      <span id="ok" class="b-shade">&nbsp;확인&nbsp;</span>
      <hr>
      <input type="password" name="pass" id="pass"
             pattern=".{4,}" placeholder="새 비밀번호 4자리 이상"
             class="text ui-widget-content ui-corner-all"><br>
      <input type="password" name="pass1" id="pass1"
             pattern=".{4,}" placeholder="한 번 더 넣으시오."
             class="text ui-widget-content ui-corner-all">
    </div>
    <div id="msg"></div>
  </div>
  <input type="hidden" id="i"    name="i"    value="$i">
  <input type="hidden" id="id"   name="id"   value="$id">
  <input type="hidden" id="nick" name="nick" value="$nick">
  <input type="hidden" id="name" name="name" value="$name">
  <input type="hidden" id="mail" name="mail" value="$mail">
  <input type="hidden" id="sure" name="sure" value="$sure">
</body>
</html>
END_OF_TEXT;
?>
