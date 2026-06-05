<?php
require_once 'functions.php';

$tex = 'p/mal.tex';
$tex_ = 'p/mal_.tex';

while (file_exists($tex)) {
  sleep(1);
}
if (!file_exists($tex_) || filemtime($tex_) < filemtime('p/mal.touched')) {
  $fp = fopen($tex, 'w');
  if (!$fp) return;
  fwrite($fp, <<<'PREAMBLE'
\documentclass[a4paper,10pt]{article}
\usepackage[top=20mm, bottom=20mm, left=20mm, right=20mm]{geometry}
\usepackage{luatexko}
\usepackage{multicol}
\usepackage{relsize}
\usepackage{hyperref}
\usepackage{textcomp}

\setmainhangulfont{Noto Serif CJK KR}[Script=Hangul]
\setsanshangulfont{Noto Sans CJK KR}[Script=Hangul]
\setmainhanjafont{Noto Serif CJK KR}

\setlength\parindent{0mm}
\setlength{\columnsep}{3mm}
\setlength{\columnseprule}{0.2mm}
\setlength{\fboxsep}{0.3mm}

\begin{document}
{\centering\LARGE\bf말다듬기\par}
\begin{multicols}{2}

PREAMBLE
  );

  $rows = selectRows(<<< SQL
SELECT e1.expr, e2.expr, n.data, n.t
FROM deals d
JOIN exprs e1 ON d.de = e1.id
JOIN exprs e2 ON d.al = e2.id
LEFT OUTER JOIN notes n ON n.deal = d.id
ORDER BY e1.expr, e2.expr, n.t
SQL
  );
  $de = '';
  $al = '';
  foreach ($rows as $row) {
    if ($de != $row[0] || $al != $row[1]) {
      $de = $row[0];
      $al = $row[1];
      fwrite($fp, '\hspace{2mm}\textbf{'.$de.'} \textrightarrow{} \textbf{'.$al."}\n\n");
    }
    if ($row[2]) {
      $t = trim($row[2]);
      $t = preg_replace('/#\{(.+)\}/', '\textbf{$1}', $t);
      $t = preg_replace('/#\((.+)\|(.+)\)/', '\href{$1}{\underline{$2}}', $t);
    $t = preg_replace('/#\((.+)\)/', '\underline{\url{$1}}', $t);
      $t = preg_replace(['/%/', '/\$/', '/#/', '/&/', '/_/', '/~/', '/\^/'],
                        ['\%', '\\\$', '\#', '\&', '\_','\~{}', '\^{}'], $t);
      fwrite($fp, $t."\n\n");
    }
  }
  fwrite($fp, <<<'CLOSING'
\end{multicols}
\end{document}

CLOSING
  );
  fclose($fp);
  exec("./pdfx.sh p/mal p > /dev/null 2>&1");
  rename($tex, $tex_);
}
?>
