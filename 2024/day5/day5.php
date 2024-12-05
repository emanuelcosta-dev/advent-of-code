<?php

$file = file_get_contents('day5.input');
$lines = explode(PHP_EOL . PHP_EOL, $file);

$rules = array_map(
  fn($line) =>
  explode('|', $line),
  explode(PHP_EOL, $lines[0])
);

$updates = array_map(
  fn($line) =>
  explode(',', $line),
  explode(PHP_EOL, $lines[1])
);

$middleSum = 0;
$orderedMiddleSum = 0;
$orderedNumbers = [];
foreach ($updates as $update) {
  if (checkCorrectOrder($update, $rules)) {
    $middleSum += getMiddleNumber($update);
  } else {
    $orderedNumbers =       orderNumbers($update, $rules);
    $orderedMiddleSum += getMiddleNumber($orderedNumbers);
  }
}

echo $middleSum;
echo PHP_EOL;
echo $orderedMiddleSum;

function orderNumbers(array $update, array $rules): array
{
  $matrix = [];
  $filled = array_fill_keys($update, 0);

  foreach ($rules as [$rule1, $rule2]) {

    if (!in_array($rule1, $update) || !in_array($rule2, $update)) {
      continue;
    }

    if (!isset($matrix[$rule1])) {
      $matrix[$rule1] = [];
    }

    $matrix[$rule1][] = $rule2;
    $filled[$rule2]++;
  }

  $list = [];
  foreach ($update as $number) {
    if ($filled[$number] === 0) {
      $list[] = $number;
    }
  }


  $orderedNumbers = [];
  while (!empty($list)) {
    $state = array_shift($list);
    $orderedNumbers[] = $state;


    if (isset($matrix[$state])) {
      foreach ($matrix[$state] as $following) {
        $filled[$following]--;
        if ($filled[$following] === 0) {
          $list[] = $following;
        }
      }
    }
  }
  return $orderedNumbers;
}


function getMiddleNumber(array $update): int
{
  return $update[floor(count($update) / 2)];
}

function checkCorrectOrder(array $update, array $rules): bool
{
  foreach ($rules as [$rule1, $rule2]) {

    if (!in_array($rule1, $update) || !in_array($rule2, $update)) {
      continue;
    }

    $rule1Position = array_search($rule1, $update);
    $rule2Position = array_search($rule2, $update);

    if ($rule1Position > $rule2Position) {
      return false;
    }
  }
  return true;
}
