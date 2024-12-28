<?php

namespace AdventOfCode2024\Day8;

class AntinodeMap
{
    private array $map = [];
    private array $frequencies = [];
    private array $antinodes = [];
    private int $rows;
    private int $cols;
    private array $data;

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

        $this->data = explode("\n", trim(file_get_contents($filePath)));
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

    public function calculateResonantAntinodes(): int
    {
        $antinodes = [];
        foreach ($this->getAntennasByFrequency() as $frequency => $antennas) {
            $count = count($antennas);

            foreach ($antennas as $antenna) {
                $antinodes["{$antenna['row']},{$antenna['col']}"] = true;
            }

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $antenna1 = $antennas[$i];
                    $antenna2 = $antennas[$j];

                    $rowDiff = $antenna2['row'] - $antenna1['row'];
                    $colDiff = $antenna2['col'] - $antenna1['col'];

                    $this->addAntinodesInDirection(
                        $antenna1['row'],
                        $antenna1['col'],
                        $rowDiff,
                        $colDiff,
                        $antinodes
                    );

                    $this->addAntinodesInDirection(
                        $antenna1['row'],
                        $antenna1['col'],
                        -$rowDiff,
                        -$colDiff,
                        $antinodes
                    );
                }
            }
        }

        return count($antinodes);
    }

    private function getAntennasByFrequency(): array
    {
        $antennas = [];

        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                $char = $this->map[$row][$col];

                if ($char !== '.' && preg_match('/^[a-zA-Z0-9]$/', $char)) {
                    $antennas[$char][] = [
                        'row' => $row,
                        'col' => $col
                    ];
                }
            }
        }

        return $antennas;
    }

    private function addAntinodesInDirection(
        int $startRow,
        int $startCol,
        int $rowDiff,
        int $colDiff,
        array &$antinodes
    ): void {
        $row = $startRow;
        $col = $startCol;

        while (true) {
            $row += $rowDiff;
            $col += $colDiff;

            if (!$this->isWithinBounds($row, $col)) {
                break;
            }

            $antinodes["$row,$col"] = true;
        }
    }
}

try {
    $antinodeMap = new AntinodeMap('day8.txt');
    $originalAntinodes = $antinodeMap->calculateAntinodes();
    $resonantAntinodes = $antinodeMap->calculateResonantAntinodes();
    echo "Number of original antinodes: " . $originalAntinodes;
    echo PHP_EOL;
    echo "Total number of antinodes including resonance: " . $resonantAntinodes;
} catch (\Throwable $th) {
    echo "Error: {$th->getMessage()}";
    exit;
}
