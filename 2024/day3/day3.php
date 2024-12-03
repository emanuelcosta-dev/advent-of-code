<?php

class FixCorruptedFiles
{
  private const MULTIPLICATION_PATTERN = '/mul\((\d{1,3}),(\d{1,3})\)/';
  private const ALL_OPERATIONS_PATTERN = "/mul\((\d{1,3}),(\d{1,3})\)|do\(\)|don't\(\)/";

  private string $inputContent;

  public function __construct(string $filePath)
  {
    $this->loadInputFile($filePath);
  }

  private function loadInputFile(string $filePath): void
  {
    if (!file_exists($filePath)) {
      throw new RuntimeException("Input file not found: {$filePath}");
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
      throw new RuntimeException("Failed to read input file: {$filePath}");
    }

    $this->inputContent = $content;
  }

  public function calculateMultiplications(): int
  {
    if (!preg_match_all(self::MULTIPLICATION_PATTERN, $this->inputContent, $matches)) {
      return 0;
    }

    return array_reduce($matches[0], function (int $carry, string $match) {
      $numbers = $this->extractNumbers($match);
      return $carry + ($numbers[0] * $numbers[1]);
    }, 0);
  }

  public function calculateEnabledMultiplications(): int
  {
    if (!preg_match_all(self::ALL_OPERATIONS_PATTERN, $this->inputContent, $matches)) {
      return 0;
    }

    $result = 0;
    $state = true;

    foreach ($matches[0] as $match) {
      match ($match) {
        'do()' => $state = true,
        "don't()" => $state = false,
        default => $result += $this->processMultiplication($match, $state)
      };
    }

    return $result;
  }

  private function extractNumbers(string $match): array
  {
    $numsComma = str_replace(['mul(', ')', '/n'], '', $match);
    return explode(',', $numsComma);
  }

  private function processMultiplication(string $match, bool $state): int
  {
    if (!str_contains($match, 'mul') || !$state) {
      return 0;
    }

    $numbers = $this->extractNumbers($match);
    return $numbers[0] * $numbers[1];
  }
}

try {
  $fixCorruptedFiles = new FixCorruptedFiles('day3.input');

  echo 'Multiplications result: ' . $fixCorruptedFiles->calculateMultiplications() . PHP_EOL;
  echo 'Enabled multiplications result: ' . $fixCorruptedFiles->calculateEnabledMultiplications() . PHP_EOL;
} catch (RuntimeException $e) {
  echo "Error: {$e->getMessage()}" . PHP_EOL;
  exit(1);
}
