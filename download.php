<?php
require_once 'functions.php';

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

$tex  = 'p/maljib.tex';
$tex_ = 'p/maljib_.tex';

while (file_exists($tex)) {
  sleep(1);
}
if (!file_exists($tex_) || filemtime($tex_) < filemtime('p/maljib.touched')) {
  $fp = fopen($tex, 'w');
  if (!$fp) return;
  fwrite($fp, <<<'PREAMBLE'
\documentclass[a4paper,10pt]{article}
\usepackage[top=20mm, bottom=20mm, left=20mm, right=20mm]{geometry}
\usepackage{kotex}
\usepackage{multicol}
\usepackage{relsize}

\setlength\parindent{2mm}
\setlength{\columnsep}{3mm}
\setlength{\columnseprule}{0.2mm}
\setlength{\fboxsep}{0.3mm}
\def\maalps#1{ \fbox{\relscale{0.85}\textbf{#1}} }

\begin{document}
{\centering\LARGE\bf배 달 말 집\par}
\begin{multicols}{2}

PREAMBLE
  );

  $rows = selectRows(<<< SQL
SELECT w.word, e.data
  FROM words w, texts e, wt
 WHERE w.id = wt.wid AND e.id = wt.id
 ORDER BY w.word
SQL
  );
  usort($rows, function($a, $b) {
    $x = &$a[0]; if ($x[0] == '-') $x = substr($x, 1);
    $y = &$b[0]; if ($y[0] == '-') $y = substr($y, 1);
    global $compare;
    return $compare($x, $y);
  });
  foreach ($rows as $row) {
    $s = preg_replace('/0*(\d+)$/', '\$^{$1}\$', $row[0]);
    $t = preg_replace('/{(.+?)}/', "\x01".'$1'."\x02", trim($row[1]));
    $t = preg_replace(array('/{/', '/}/', '/#/', '/\$/', '/≈/u'),  // , '/☛/u'
                      array('\{', '\}', '\#', '\\\$', '$\approx$'), $t); // , '☞'
    $t = preg_replace(array('/</', '/>/'), array('$<$', '$>$'), $t);
    $t = preg_replace('/([〕①-⑳㉑-㉟㊱-㊿])\s*(\(.+?\))\s*/u', '$1\textit{$2}', $t);
    $t = preg_replace('/\x01(.+?)\x02/', '\textbf{$1}', $t);
    $t = preg_replace('/(\p{Hangul})0*(\d+)/u', '$1\$^{$2}\$', $t);
    $t = preg_replace_callback('/꿈】\s*[^【〔]+/u', function($u) {
      return preg_replace('/(】)\s*/', '$1',
             preg_replace('/\s*(\(.+?\))\s*/', ' \textit{$1} ', $u[0]));
    }, $t);
    $t = preg_replace_callback('/말】(\s*[^【]+)+/u', function($u) {
      return preg_replace('/([】.》〉])\s*([^.》〉:]+?)\s*[:]\s*/u',
      // return preg_replace('/([】.])\s*(([^.:]|\(.*?\))+)\s*[:]\s*/u',
                          '$1\newline\textbf{$2}\hspace{.5mm}: ', $u[0]);
    }, $t);
    $t = preg_replace('/\s*(【)/u', '\newline$1', $t);  // 〔|
    $t = preg_replace('/\s*〔(.+?)〕\s*/', '\maalps{$1}', $t);
    fwrite($fp, '\textbf{'."$s} $t\n\n");
  }
  fwrite($fp, <<<'CLOSING'
\end{multicols}
\end{document}

CLOSING
  );
  fclose($fp);
  $cwd = getcwd();
  exec("$cwd/pdfx $cwd/p/maljib $cwd/p 2>&1 >/dev/null");
  rename($tex, $tex_);
}
?>
