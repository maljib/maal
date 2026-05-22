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


$collator = new Collator('root');
$collator->setStrength(Collator::PRIMARY);
$collator->sort($items);

function getSortKey($str) {
  if ($str === '') {
    return ['', 0, ''];
  }
  
  $first_char = mb_substr($str, 0, 1, 'UTF-8');
  $code_point = mb_ord($first_char, 'UTF-8');
  
  // Standalone Jamo
  if (0x3131 <= $code_point && $code_point <= 0x3163) {
    return [$first_char, 0, $str];
  }

  // Hangul Syllables
  if (0xAC00 <= $code_point && $code_point <= 0xD7A3) {
    $choseong_list = ['ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ',
                      'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'];
    return [$choseong_list[(int) (($code_point - 0xAC00) / 588)], 1, $str];
  }
  
  // Non-Hangul Characters (Using Hangul Filler to position them after 'ㅣ')
  return ["\u{3164}", 2, $str];
}

$compare = function($a, $b) use ($collator) {
  $key_a = getSortKey($a);
  $key_b = getSortKey($b);

  if ($key_a[0] !== $key_b[0]) {
    return $key_a[0] <=> $key_b[0];
  }
  if ($key_a[1] === 2) {
    return $collator->compare($key_a[2], $key_b[2]);
    // return strcasecmp($key_a[2], $key_b[2]);
  }
  if ($key_a[1] !== $key_b[1]) {
    return $key_a[1] <=> $key_b[1];
  }
  return $key_a[2] <=> $key_b[2];
};

$items = ['ㅏ', 'ㄾ', '감', 'P', 'a', 'ㄴ', 'x', '가', 
          'ㄱ', '나', 'ㄷ', 'orange', 'Apple', 'ㅣ', 'ㄳ', 'ㅄ', 'ㄲ'];;
print(implode(", ", $items)."\n");
usort($items, $compare);
print(implode(", ", $items)."\n");
?>