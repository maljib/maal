<?php
require_once 'functions.php';

$tex = 'p/maljib.tex';

function toPdf() {
  global $tex;
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

  $rows = selectRows('w.word, e.data', 'words w, texts e, wt',
                     'w.id = wt.wid and e.id = wt.id ORDER BY w.word');
  usort($rows, function($a, $b) {
    $x = &$a[0]; if ($x[0] == '-') $x = substr($x, 1);
    $y = &$b[0]; if ($y[0] == '-') $y = substr($y, 1);
    return strcmp($x, $y);
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
  rename($tex, 'p/_maljib.tex');
}

while (file_exists($tex)) {
  sleep(1);
}
$time = filemtime('p/maljib.t');
if (filemtime('p/maljib.pdf') < $time) {
  toPdf();
}
date_default_timezone_set('Asia/Seoul');
echo date("ymdHi", $time);
?>
