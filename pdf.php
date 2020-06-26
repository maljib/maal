<?php
require_once 'functions.php';

$tex = 'p/mal.tex';
$pdf = 'p/mal.pdf';

while (file_exists($tex)) {
  sleep(1);
}
if (!file_exists($pdf) || filemtime($pdf) < filemtime('p/mal.t')) {
  $fp = fopen($tex, 'w');
  if (!$fp) return;
  fwrite($fp, <<<'PREAMBLE'
\documentclass[a4paper,10pt]{article}
\usepackage[top=20mm, bottom=20mm, left=20mm, right=20mm]{geometry}
\usepackage{kotex}
\usepackage{multicol}
\usepackage{relsize}
\usepackage{hyperref}
\usepackage{textcomp}

\setlength\parindent{0mm}
\setlength{\columnsep}{3mm}
\setlength{\columnseprule}{0.2mm}
\setlength{\fboxsep}{0.3mm}
\def\maalps#1{ \fbox{\relscale{0.85}\textbf{#1}} }

\begin{document}
{\centering\LARGE\bf깨끗한 우리말 쓰기\par}
\begin{multicols}{2}

PREAMBLE
  );

  $rows = selectRows(<<< SQL
SELECT e1.expr, e2.expr, n.data, n.t
  FROM deals d JOIN exprs e1 ON d.de = e1.id
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
      $t = preg_replace(array('/%/', '/$/', '/#/', '/&/', '/_/', '/~/', '/\^/'),
                        array('\%', '\$', '\#', '\&', '\_','\~{}', '\^{}'), $t);
      fwrite($fp, $t."\n\n");
    }
  }
  fwrite($fp, <<<'CLOSING'
\end{multicols}
\end{document}

CLOSING
  );
  fclose($fp);
  $cwd = getcwd();
  exec("$cwd/pdfx $cwd/p/mal $cwd/p 2>&1 >/dev/null");
  rename($tex, 'p/_mal.tex');
}
?>
