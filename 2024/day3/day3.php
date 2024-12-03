<?php
$file = file_get_contents('day3.input');

$count = preg_match_all('/mul\((\d{1,3}),(\d{1,3})\)/', $file, $matches);
$numbers = '';
$result = 0;
foreach ($matches[0] as $match) {
  $numsComma = str_replace(['mul(', ')', '/n'], '', $match);
  $numbers = explode(',', $numsComma);
  $result += $numbers[0] * $numbers[1];
}

echo 'Result is ' . $result;
