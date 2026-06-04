<?php
echo "<pre>";

$d = "maljib";
$start = hrtime(true);
exec("cd p && mv {$d}_.tex $d.tex && ../pdfx.sh $d . 2>&1; mv $d.tex {$d}_.tex",
    $output);
$end = hrtime(true);
echo "Elapsed time for PDF generation: " . (($end - $start) / 1e+9) . " s\n";
echo implode("\n", $output);

require_once 'compare.php';
$list =  ['ㅣ', 'x', 'ㅏ', '달', 'ㄾ', '감', 'P', 'a', 'ㄴ', '가', 'ㄱ',
          'à', '나', 'ㄷ', 'orange', 'Apple', 'ㄳ', 'ㅄ', 'Á', 'ㄲ'];
print(implode(", ", $list)." - 정렬하기 전\n");
$collator->sort($list);
print(implode(", ", $list). " - 루트 로케일 사용 정렬\n");
usort($list, $compare);
print(implode(", ", $list)." - 사용자 정의 오름순 정렬\n");
usort($list, function($a, $b) {
  global $compare;
  return $compare($b, $a);
});
print(implode(", ", $list)." - 사용자 정의 내림순 정렬\n");

$time = time();
date_default_timezone_set('Asia/Seoul');
print_r(date("Y-m-d H:i", $time)."\n");
$stack = ["orange", 1, "만화"];
array_push($stack, ["t", 2, "화"]);
$s = ["orange", 1, "만화"];
$s[] = ["o", 0, "만"];
print_r($stack);
print_r($s);

$as = [[1,2,3], [4,5,6], [7,8,9]];
for ($c = count($as), $i = 0; $i < $c; $i++) {
  $as[$i][] = [$i, $i+1];
}
print_r($as);

echo "</pre>";
?>
