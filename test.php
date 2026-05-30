<?php
$time = time();
date_default_timezone_set('Asia/Seoul');
print_r(date("Y-m-d_H:i", $time)."\n");
$stack = array("orange", 1, "만화");
array_push($stack, array("t", 2, "화"));

$s = array("orange", 1, "만화");
$s[] = array("o", 0, "만");

print_r($stack);
print_r($s);

$as = array(
  array(1,2,3),
  array(4,5,6),
  array(7,8,9)
);
for ($c = count($as), $i = 0; $i < $c; $i++) {
  $as[$i][] = array($i, $i+1);
}
print_r($as);
print("<br>");

$password = "9521gh";
$hash = password_hash($password, PASSWORD_DEFAULT);
print($password . " => " . $hash . "\n");
print("<br>");
print(password_verify($password, $hash)? "OK\n": "NO\n");
print("<br>");

require_once 'compare.php';

$list =  ['ㅣ', 'x', 'ㅏ', '달', 'ㄾ', '감', 'P', 'a', 'ㄴ', '가', 'ㄱ',
          'à', '나', 'ㄷ', 'orange', 'Apple', 'ㄳ', 'ㅄ', 'Á', 'ㄲ'];
print(implode(", ", $list)." - 정렬하기 전<br>\n");

$collator->sort($list);
print(implode(", ", $list). " - 루트 로케일 사용 정렬<br>\n");

usort($list, $compare);
print(implode(", ", $list)." - 사용자 정의 오름순 정렬<br>\n");

usort($list, function($a, $b) {
  global $compare;
  return $compare($b, $a);
});
print(implode(", ", $list)." - 사용자 정의 내림순 정렬<br>\n");
?>