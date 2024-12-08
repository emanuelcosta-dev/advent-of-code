<?php

namespace AdventOfCode2024\Day8;

class AntinodeMap
{
    private array $map = [];
    private array $frequencies = [];
    private array $antinodes = [];
    private int $rows;
    private int $cols;

    public function __construct($filePath)
    {
        $this->loadFile($filePath);
        $this->getAllFrequencies();
    }

    public function loadFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Input file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read input file: {$filePath}");
        }

        $this->map = array_map('str_split', explode(PHP_EOL, trim($content)));
        $this->rows = count($this->map);
        $this->cols = count($this->map[0]);
    }

    private function getAllFrequencies(): void
    {
        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                $char = $this->map[$row][$col];
                if ($char !== ".") {
                    $this->frequencies[$char][] = ['row' => $row, 'col' => $col];
                }
            }
        }
    }

    public function calculateAntinodes(): int
    {
        foreach ($this->frequencies as $antennas) {
            $count = count($antennas);
            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $this->findAntinodes($antennas[$i], $antennas[$j]);
                }
            }
        }

        return count($this->antinodes);
    }

    private function findAntinodes(array $antenna1, array $antenna2): void
    {
        $distance = $this->calculateDistance($antenna1, $antenna2);

        $this->validateAntinode($antenna1, $antenna2, $distance);
        $this->validateAntinode($antenna2, $antenna1, $distance);
    }

    private function calculateDistance(array $antenna1, array $antenna2): float
    {
        $rowDiff = $antenna2['row'] - $antenna1['row'];
        $colDiff = $antenna2['col'] - $antenna1['col'];
        return sqrt($rowDiff * $rowDiff + $colDiff * $colDiff);
    }

    private function validateAntinode(array $near, array $far, float $distance): void
    {
        $distanceCol = $far['col'] - $near['col'];
        $distanceRow = $far['row'] - $near['row'];

        $length = sqrt($distanceCol * $distanceCol + $distanceRow * $distanceRow);
        $distanceCol /= $length;
        $distanceRow /= $length;

        $antinodeRow = (int)round($near['row'] + $distanceRow * $distance * 2);
        $antinodeCol = (int)round($near['col'] + $distanceCol * $distance * 2);

        if ($this->isWithinBounds($antinodeRow, $antinodeCol)) {
            $this->antinodes["$antinodeRow,$antinodeCol"] = true;
        }
    }

    private function isWithinBounds(int $row, int $col): bool
    {
        return $row >= 0 && $row < $this->rows && $col >= 0 && $col < $this->cols;
    }
}

try {
    $antinodeMap = new AntinodeMap('day8.txt');
    $originalAntinodes = $antinodeMap->calculateAntinodes();
    echo "Number of original antinodes: " . $originalAntinodes;
} catch (\Throwable $th) {
    echo "Error: {$th->getMessage()}";
    exit;
}
