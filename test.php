<?php
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

//phpinfo();
?>