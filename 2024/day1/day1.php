<?php

$file = file_get_contents('day1.input');
$lines = explode(PHP_EOL, $file);

foreach ($lines as $line) {
  list($num1, $num2) = explode("   ", $line);
  $col1[] = (int)$num1;
  $col2[] = (int)$num2;
}

sort($col1);
sort($col2);

for ($i = 0; $i < count($col1); $i++) {
  $diff[] = abs($col1[$i] - $col2[$i]);
}

$sum = array_sum($diff);
echo "Total distance: " . $sum . PHP_EOL;

$frequency = array_count_values($col2);
(int)$similarity = 0;

foreach ($col1 as $number) {
  if (isset($frequency[$number])) (int)$similarity += ($number * $frequency[$number]);
}

echo "Similarity score: " . $similarity;
