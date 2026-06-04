<?php
require_once 'functions.php';
require_once 'compare.php';

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
\usepackage{luatexko}
\usepackage{multicol}
\usepackage{relsize}

\setmainhangulfont{Noto Serif CJK KR}[Script=Hangul]
\setsanshangulfont{Noto Sans CJK KR}[Script=Hangul]
\setmainhanjafont{Noto Serif CJK KR}

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
    $t = preg_replace(['/{/', '/}/', '/#/', '/\$/', '/≈/u'],  // , '/☛/u'
                      ['\{', '\}', '\#', '\\\$', '$\approx$'], $t); // , '☞'
    $t = preg_replace(['/</', '/>/'], ['$<$', '$>$'], $t);
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
  exec("./pdfx.sh p/maljib p > /dev/null 2>&1");
  rename($tex, $tex_);
}
?>
