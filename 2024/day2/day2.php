<?php

class ReportValidator
{
  private array $reports = [];

  public function __construct(string $filePath)
  {
    $this->loadReports($filePath);
  }

  private function loadReports(string $filePath): void
  {
    $fileContent = file_get_contents($filePath);
    $rows = explode(PHP_EOL, $fileContent);
    foreach ($rows as $row) {
      $this->reports[] = explode(" ", $row);
    }
  }

  public function validateReports(): void
  {
    $reportNumber = 1;
    $perfectValidCount = 0;
    $failedOnceCount = 0;

    foreach ($this->reports as $report) {
      $isPerfectlyValid = $this->isValidSequence($report);
      $canBeMadeValid = $this->canBeMadeValid($report);

      if ($isPerfectlyValid) {
        $perfectValidCount++;
      } elseif ($canBeMadeValid) {
        $failedOnceCount++;
      }

      $reportNumber++;
    }
    $problemDampenerCount = $perfectValidCount + $failedOnceCount;

    echo "Perfectly valid reports: {$perfectValidCount}" . PHP_EOL;
    echo "Reports valid after problem dampener: {$problemDampenerCount}" . PHP_EOL;
  }

  private function isValidSequence(array $sequence): bool
  {
    if (count($sequence) < 2) {
      return true;
    }

    $increasing = null;
    for ($i = 1, $count = count($sequence); $i < $count; $i++) {
      $diff = $sequence[$i] - $sequence[$i - 1];
      $validDiff = abs($diff) >= 1 && abs($diff) <= 3;

      if (!$validDiff) {
        return false;
      }

      if ($increasing === null) {
        $increasing = $diff > 0;
      } elseif (($diff > 0) !== $increasing) {
        return false;
      }
    }
    return true;
  }

  private function canBeMadeValid(array $sequence): bool
  {
    for ($i = 0, $count = count($sequence); $i < $count; $i++) {
      $testSequence = array_values(array_filter($sequence, fn($key) => $key !== $i, ARRAY_FILTER_USE_KEY));

      if ($this->isValidSequence($testSequence)) {
        return true;
      }
    }
    return false;
  }
}

$validator = new ReportValidator('day2.input');
$validator->validateReports();
