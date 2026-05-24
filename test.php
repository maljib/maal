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


$collator = new Collator('root');
$collator->setStrength(Collator::PRIMARY);

$choseong =  ['ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ',
              'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'];
$interval =  (0xD7A4 - 0xAC00) / count($choseong);

function getDictionaryOrderKey($str) {
  if ($str === '') return [0, '', ''];                  // ""

  global $choseong, $interval;  // 초성 목록, 같은 초성으로 시작하는 음절 개수
  $first_char = mb_substr($str, 0, 1, 'UTF-8');         // 첫 글자
  $code_point = mb_ord($first_char, 'UTF-8');           // 첫 글자 코드 포인트

  if (0x3131 <= $code_point && $code_point < 0x3164) {  // 첫 글자가 한글 자모
    return [0, $first_char, $str];
  }
  if (0xAC00 <= $code_point && $code_point < 0xD7A4) {  // 첫 글자가 한글 음절
    return [1, $choseong[(int) (($code_point - 0xAC00) / $interval)], $str];
  }
  return [2, "\u{3164}", $str];                         // 첫 글자가 한글 아님
}

$compare = function($a, $b) use ($collator) {
  $key_a = getDictionaryOrderKey($a);  // [유형, (초성) 자모, 원본 문자열]
  $key_b = getDictionaryOrderKey($b);  // 유형: 0=자모, 1=음절, 2=한글이 아님

  if ($key_a[1] !== $key_b[1]) {
    return $key_a[1] <=> $key_b[1];  // "", 한글, 한글이 아닌 것 순서
  }
  if ($key_a[0] !== $key_b[0]) {
    return $key_a[0] <=> $key_b[0];  // 한글 자모 다음에 초성이 그 자모인 음절
  }
  if ($key_a[0] !== 2) {
    return $key_a[2] <=> $key_b[2];  // 한글 가나다순
  }
  return $collator->compare($key_a[2], $key_b[2]);  // 한글 아닌 것 사전식 순서
};

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