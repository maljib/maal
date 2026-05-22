<?php
require_once 'functions.php';

$collator = new Collator('root');
$collator->setStrength(Collator::PRIMARY);

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

$tex = 'p/maljib.tex';
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
  usort($rows, function($a, $b) use ($collator) {
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

    $x = &$a[0]; if ($x[0] == '-') $x = substr($x, 1);
    $y = &$b[0]; if ($y[0] == '-') $y = substr($y, 1);
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
