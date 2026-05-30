<?php // functions.php
require_once '../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . "/..")->load();

$connection = new mysqli('localhost',
                  $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
$connection->connect_error and die($connection->connect_error);

// Sql을 실행한다
function sql($query) {
  global $connection;
  try {
    return $connection->query($query);
  } catch (mysqli_sql_exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    error_log("Failed SQL: " . $query);
    throw $e;
  }
}

function selectRows($s) {
  $result = sql($s);
  for ($rows = []; $row = $result->fetch_row(); $rows[] = $row);
  $result->close();
  return $rows;
}

function selectValues($s) {
  $result = sql($s);
  for ($values = []; $row = $result->fetch_row(); $values[] = $row[0]);
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
  $sql = "INSERT INTO $table($columns) VALUES($values)";
  try {
    $connection->query($sql);
    return $connection->insert_id;
  } catch (mysqli_sql_exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    error_log("Failed SQL: " . $sql);
    throw $e;
  }
}

function sqlUpdate($table, $set, $where) {
  global $connection;
  $sql = "UPDATE $table SET $set WHERE $where";
  try {
    $connection->query($sql);
    return $connection->affected_rows;
  } catch (mysqli_sql_exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    error_log("Failed SQL: " . $sql);
    throw $e;
  }
}

function sqlDelete($table, $where) {
  global $connection;
  $sql = "DELETE FROM $table WHERE $where";
  try {
    $connection->query($sql);
    return $connection->affected_rows;
  } catch (mysqli_sql_exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    error_log("Failed SQL: " . $sql);
    throw $e;
  }  
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
  touch('p/maljib.touched');
} 

function touchMal() {
  touch('p/mal.touched');
} 
?>
