<?php

class WordSearch
{

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


  public function XMASOccurences()
  {
    $lines = explode(PHP_EOL, $this->inputContent);
    $lineCount = count($lines);
    $colCount = strlen($lines[0]);
    $word = 'XMAS';
    $wordLenght = strlen($word);
    $occurence = 0;

    for ($line = 0; $line < $lineCount; $line++) {
      for ($col = 0; $col < $colCount; $col++) {
        // horizontal right
        if ($col <= $colCount - $wordLenght && substr($lines[$line], $col, $wordLenght) == $word) {
          $occurence++;
        }
        // horizontal left
        if ($col >= $wordLenght - 1 && substr($lines[$line], $col - ($wordLenght - 1), $wordLenght) == strrev($word)) {
          $occurence++;
        }
        // vertical down
        if ($line <= $lineCount - $wordLenght) {
          $verticalWord = '';
          for ($i = 0; $i < $wordLenght; $i++) {
            $verticalWord .= $lines[$line + $i][$col];
          }
          if ($verticalWord == $word) {
            $occurence++;
          }
        }
        // vertical up
        if ($line >= $wordLenght - 1) {
          $verticalWord = '';
          for ($i = 0; $i < $wordLenght; $i++) {
            $verticalWord .= $lines[$line - $i][$col];
          }
          if ($verticalWord == $word) {
            $occurence++;
          }
        }
        // diagonal down right
        if ($line <= $lineCount - $wordLenght && $col <= $lineCount - $wordLenght) {
          $diagonalWord = '';
          for ($i = 0; $i < $wordLenght; $i++) {
            $diagonalWord .= $lines[$line + $i][$col + $i];
          }
          if ($diagonalWord == $word) {
            $occurence++;
          }
        }
        // diagonal up right
        if ($line >= $wordLenght - 1 && $col <= $lineCount - $wordLenght) {
          $diagonalWord = '';
          for ($i = 0; $i < $wordLenght; $i++) {
            $diagonalWord .= $lines[$line - $i][$col + $i];
          }
          if ($diagonalWord == $word) {
            $occurence++;
          }
        }
        // diagonal down left
        if ($line <= $lineCount - $wordLenght && $col >= $wordLenght - 1) {
          $diagonalWord = '';
          for ($i = 0; $i < $wordLenght; $i++) {
            $diagonalWord .= $lines[$line + $i][$col - $i];
          }
          if ($diagonalWord == $word) {
            $occurence++;
          }
        }
        // diagonal up left
        if ($line >= $wordLenght - 1 && $col >= $wordLenght - 1) {
          $diagonalWord = '';
          for ($i = 0; $i < $wordLenght; $i++) {
            $diagonalWord .= $lines[$line - $i][$col - $i];
          }
          if ($diagonalWord == $word) {
            $occurence++;
          }
        }
      }
    }
    return $occurence;
  }

  public function XMASPuzzle(): int
  {
    $lines = explode(PHP_EOL, $this->inputContent);
    $lineCount = count($lines);
    $colCount = strlen($lines[0]);
    $count = 0;
    $patterns = $this->puzzlePatterns();

    for ($line = 1; $line < $lineCount - 1; $line++) {
      for ($col = 1; $col < $colCount - 1; $col++) {
        if ($lines[$line][$col] !== 'A') {
          continue;
        }

        foreach ($patterns as $pattern) {
          if (
            !($this->validateMAS($lines[$line - 1][$col - 1], $pattern[0][0]) &&
              $this->validateMAS($lines[$line][$col], $pattern[0][1]) &&
              $this->validateMAS($lines[$line + 1][$col + 1], $pattern[0][2]))
          ) {
            continue;
          }
          if (
            !($this->validateMAS($lines[$line + 1][$col - 1], $pattern[1][0]) &&
              $this->validateMAS($lines[$line][$col], $pattern[1][1]) &&
              $this->validateMAS($lines[$line - 1][$col + 1], $pattern[1][2]))
          ) {
            continue;
          }
          $count++;
        }
      }
    }
    return $count;
  }

  private function validateMAS(string $letter, string $target): bool
  {
    return ($letter === 'M' && $target === 'M') || ($letter === 'A' && $target === 'A') || ($letter === 'S' && $target === 'S');
  }

  private function puzzlePatterns(): array
  {
    $patterns = [
      [['M', 'A', 'S'], ['M', 'A', 'S']],
      [['M', 'A', 'S'], ['S', 'A', 'M']],
      [['S', 'A', 'M'], ['M', 'A', 'S']],
      [['S', 'A', 'M'], ['S', 'A', 'M']]
    ];

    return $patterns;
  }
}


try {
  $wordSearch = new WordSearch('day4.input');

  echo 'XMAS occurences: ' . $wordSearch->XMASOccurences() . PHP_EOL;
  echo 'MAS occurences: ' . $wordSearch->XMASPuzzle() . PHP_EOL;
} catch (RuntimeException $e) {
  echo "Error: {$e->getMessage()}" . PHP_EOL;
  exit(1);
}
