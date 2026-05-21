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

$collator = Collator::create('ko_KR');

$xxx = ["사람", "ㅅ변격", "감사", "나무", "ㄴ", "ㄱ값", "가을"];
function getSortKey($str) {
  if ($str === '') {
    return ['', 0, ''];
  }
  $firstChar = mb_substr($str, 0, 1, 'UTF-8');
  $codePoint = mb_ord($firstChar, 'UTF-8');
  if (0x1100 <= $codePoint && $codePoint <= 0x11FF ||
      0x3131 <= $codePoint && $codePoint <= 0x318E ||
      0xA960 <= $codePoint && $codePoint <= 0xA97C ||
      0xD7B0 <= $codePoint && $codePoint <= 0xD7FB) {
    return [$firstChar, 0, $str];
  }
  $jamoList = ['ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ',
              'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'];
  if (0xAC00 <= $codePoint && $codePoint <= 0xD7A3) {
    return [$jamoList[(int) (($codePoint - 0xAC00) / 588)], 1, $str];
  }
  return [$firstChar, 2, $str];
}

usort($xxx, function($a, $b) use($collator) {
  $keyA = getSortKey($a);
  $keyB = getSortKey($b);
  if ($keyA[0] !== $keyB[0]) {
    return $collator->compare($keyA[0], $keyB[0]);
  }
  if ($keyA[1] !== $keyB[1]) {
    return $collator->compare($keyA[1], $keyB[1]);
  }
  return $collator->compare($keyA[2], $keyB[2]);
});
print_r($xxx);

//phpinfo();
// https://gemini.google.com/app/06ef96356ed427c7   Free LAMP Server
?>



