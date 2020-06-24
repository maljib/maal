<?php // functions.php

$dbhost  = 'localhost';
$dbuser  = 'scott';
$dbpass  = 'tiger';
$dbname  = 'wordlist';

$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
$connection->connect_error and die($connection->connect_error);

// Sql을 실행한다
function sql($query) {
  global $connection;
  $result = $connection->query($query) or die($connection->error);
  return $result;
}

function selectRows($s) {
  $result = sql($s);
  for ($rows = array(); $row = $result->fetch_row(); $rows[] = $row);
  $result->close();
  return $rows;
}

function selectValues($s) {
  $result = sql($s);
  for ($values = array(); $row = $result->fetch_row(); $values[] = $row[0]);
  $result->close();
  return $values;
}

function selectRow($s) {
  $result = sql($s);
  $row = $result->fetch_row();
  $result->close();
  return $row;
}

function selectValue($s) {
  $row = selectRow($s);
  return $row? $row[0]: '';
}

function sqlInsert($table, $columns, $values) {
  global $connection;
  $connection->query("INSERT INTO $table($columns) VALUES($values)")
              or die($connection->error);
  return $connection->insert_id;
}

function sqlUpdate($table, $set, $where) {
  global $connection;
  $connection->query("UPDATE $table SET $set WHERE $where")
              or die($connection->error);
  return $connection->affected_rows;
}

function sqlDelete($table, $where) {
  global $connection;
  $connection->query("DELETE FROM $table WHERE $where")
              or die($connection->error);
  return $connection->affected_rows;
}

// 특수 글자 등을 escape 처리하여 ASCII 글자열로 변환한다
function escapeString($string) {
  global $connection;
  return $connection->escape_string($string);
}

function cat($a, $b) {
  return $a && $b? $a.','.$b: $a.$b;
}

function getPost($index) {
  return isset($_POST[$index])? $_POST[$index]: '';
}

function getGet($index) {
  return isset($_GET[$index])? $_GET[$index]: '';
}

// 비밀번호의 해시 값을 구한다 (비밀번호)
function getHash($password) {
  // $salt = random_bytes(16); 
  $salt = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
  return hash_pbkdf2("sha256", $password, $salt, 1000, 16, true).$salt;
}

// 비밀번호의 해시 값을 검사한다 (비밀번호, 해시 값)
function checkPass($password, $hash) {
  return hash_pbkdf2("sha256", $password, substr($hash, 16), 1000, 16, true)
         === substr($hash, 0, 16);
}

function mess($s) {
  if ($s && is_string($s)) {
    $a = str_split($s);
    foreach ($a as $i=>&$c) {
      $o = ord($c);
      if (0x20 <= $o && $o <= 0x7f) {
        $c = chr($o ^ ($i % 29 + 2));
      }
    }
    return join($a);
  }
  return $s;
}

function selectEtc($i) {
  $row = selectRow("SELECT user, data FROM etc WHERE id = $i")
            or die('이미 확인되었습니다.');
  return $row;
}

function deleteEtc($i) {
  sqlDelete('etc', "id=$i") or die('etc 데이터를 지울 수 없습니다.');
}

function touchMaljib() {
  touch('p/maljib.t');
} 

function touchMal() {
  touch('p/mal.t');
} 
?>
