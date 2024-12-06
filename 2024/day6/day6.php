<?php

namespace AdventOfCode2024\Day6;

enum Direction: int
{
    case NORTH = 0;
    case EAST = 1;
    case SOUTH = 2;
    case WEST = 3;
}

class GuardPatrol
{
    private readonly array $directions;
    private array $startPosition;

    public function __construct(
        private  array $map,
        private readonly int $rows,
        private readonly int $cols
    ) {
        $this->directions = [
            Direction::NORTH->value => [-1, 0],
            Direction::EAST->value => [0, 1],
            Direction::SOUTH->value => [1, 0],
            Direction::WEST->value => [0, -1],
        ];
        $this->findStartPosition();
    }

    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Input file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read input file: {$filePath}");
        }

        $map = array_map('str_split', explode(PHP_EOL, trim($content)));
        return new self(
            map: $map,
            rows: count($map),
            cols: count($map[0])
        );
    }

    private function findStartPosition(): void
    {
        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                if ($this->map[$row][$col] === '^') {
                    $this->startPosition = [$row, $col];
                    $this->map[$row][$col] = '.';
                    return;
                }
            }
        }
        throw new \RuntimeException('No starting position found in map');
    }

    public function findLoopPositions(): array
    {
        $positions = $this->moveForward($this->startPosition[0], $this->startPosition[1]);
        if (!$positions) {
            return [
                'count' => 0,
                'positions' => [],
                'distinctCount' => 0
            ];
        }

        $distinctPositions = [[$this->startPosition[0], $this->startPosition[1]]];
        $loopPositions = [];

        foreach ($positions as $p => $_) {
            $row = $p >> 8;
            $col = $p & 0xFF;
            $distinctPositions[] = [$row, $col];

            if ($this->shouldSkipPosition($row, $col)) {
                continue;
            }

            if ($this->createsLoop($row, $col)) {
                $loopPositions[] = [$row, $col];
            }
        }

        return [
            'count' => count($loopPositions),
            'positions' => $loopPositions,
            'distinctCount' => count($distinctPositions)
        ];
    }

    private function shouldSkipPosition(int $row, int $col): bool
    {
        return $this->map[$row][$col] === '#' ||
            ($row === $this->startPosition[0] && $col === $this->startPosition[1]);
    }

    private function createsLoop(int $row, int $col): bool
    {
        $this->map[$row][$col] = '#';
        $result = !$this->moveForward($this->startPosition[0], $this->startPosition[1]);
        $this->map[$row][$col] = '.';
        return $result;
    }

    private function moveForward(int $startRow, int $startCol): array|false
    {
        $dir = Direction::NORTH->value;
        $visited = [];
        $currentPosition = ['row' => $startRow, 'col' => $startCol];

        while ($this->canMove($currentPosition, $dir)) {
            $nextPosition = $this->calculateNextPosition($currentPosition, $dir);

            if ($this->isObstacle($nextPosition)) {
                $dir = ($dir + 1) % 4;
                continue;
            }

            $currentPosition = $nextPosition;
            $hash = ($currentPosition['row'] << 8) + $currentPosition['col'];

            if (!isset($visited[$hash])) {
                $visited[$hash] = 0;
            } elseif ($visited[$hash] & (1 << $dir)) {
                return false;
            }

            $visited[$hash] |= (1 << $dir);
        }

        return $visited;
    }

    private function canMove(array $position, int $dir): bool
    {
        $next = $this->calculateNextPosition($position, $dir);
        return $next['row'] >= 0 &&
            $next['col'] >= 0 &&
            $next['row'] < $this->rows &&
            $next['col'] < $this->cols;
    }

    private function calculateNextPosition(array $position, int $dir): array
    {
        return [
            'row' => $position['row'] + $this->directions[$dir][0],
            'col' => $position['col'] + $this->directions[$dir][1]
        ];
    }

    private function isObstacle(array $position): bool
    {
        return $this->map[$position['row']][$position['col']] === '#';
    }
}

try {
    $guardPatrol = GuardPatrol::fromFile('day6.input');
    $result = $guardPatrol->findLoopPositions();

    echo sprintf(
        "Distinct positions visited: %d\nNumber of possible loop positions: %d\n",
        $result['distinctCount'],
        $result['count']
    );
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
