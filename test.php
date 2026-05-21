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




$items = ['ㅏ', 'ㄾ', '감', 'ㄴ', '가', 'ㄱ', '나', 'ㄷ', 'ㅣ', 'ㄳ', 'ㅄ', 'ㄲ'];

$collator = new Collator('ko_KR');
$collator->sort($items);

print(implode(", ", $items));
print_r($items); 
// Result: [ㄱ, 가, 감, ㄲ, ㄴ, 나, ㄷ, ㅄ, ㅏ, ㅣ, ㄳ, ㄾ]

function getSortKey($str) {
  if ($str === '') {
    return ['', 0, ''];
  }
  
  $first_char = mb_substr($str, 0, 1, 'UTF-8');
  $code_point = mb_ord($first_char, 'UTF-8');
  
  // If the first character is a standalone Jamo
  if (0x3131 <= $code_point && $code_point <= 0x3163) {
    return [$first_char, 0, $str];
  }

  // If the first character is a Hangul syllable
  if (0xAC00 <= $code_point && $code_point <= 0xD7A3) {
    $choseong_ㅣist = ['ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ',
                        'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'];
    return [$choseong_ㅣist[(int) (($code_point - 0xAC00) / 588)], 1, $str];
  }
  
  // Otherwise
  return [$first_char, 2, $str];
}

$compare = function($a, $b) {
  $key_a = getSortKey($a);
  $key_b = getSortKey($b);
  if ($key_a[0] !== $key_b[0]) {
    return $key_a[0] <=> $key_b[0];;
  }
  if ($key_a[1] !== $key_b[1]) {
    return $key_a[1] <=> $key_b[1];
  }
  return $key_a[2] <=> $key_b[2];
};

usort($items, $compare);

print(implode(", ", $items));
// Result: [ㄱ, 가, 감, ㄲ, ㄳ, ㄴ, 나, ㄷ, ㄾ, ㅄ, ㅏ, ㅣ]


//phpinfo();
// https://gemini.google.com/app/06ef96356ed427c7   Free LAMP Server
?>



