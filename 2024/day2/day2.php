<?php

/*
 * Get rows from file
 * ['7 6 4 2 1', '1 2 7 8 9', '9 7 6 2 1', '1 3 2 4 5']
 * [
 *  '1' => [
 *   '7',
 *   '6',
 *   '4',
 *   '2',
 *   '1'
 *  ],
 *  '2' => [
 *    
 *  ]
 *  ]
 *
 */

$file = file_get_contents('day2.input');
$rows = explode(PHP_EOL, $file);
foreach ($rows as $row) {
  $reports[] = explode(" ", $row);
}

$validReports = 0;
$reportNumber = 1;

foreach ($reports as $report) {
  $lastLevel = null;
  $valid = true;
  $order = '';
  foreach ($report as $level) {
    if ($lastLevel === null) {
      $lastLevel = $level;
      continue;
    }

    $diff = $level - $lastLevel;

    if ($diff >= 1 && $diff <= 3) {
      $order = $order === 'desc' ? false : "asc";
    } elseif ($diff <= -1 && $diff >= -3) {
      $order = $order === 'asc' ? false : "desc";
    } else {
      $valid = false;
      break;
    }

    if ($order === false) {
      $valid = false;
      break;
    }
    $lastLevel = $level;
  }
  $reportLine = implode(',', $report);
  if ($valid) echo "Report " . $reportNumber . " is valid." . $reportLine . PHP_EOL;
  if ($valid) $validReports++;
  $reportNumber++;
}
//print_r($reports);
echo "Valid reports" . $validReports;
// Correct answer is 479
