<?php
// find the ^
// Go up until you find a #
// Turn 90 degrees clockwise

class GuardPatrol
{
    private array $map;
    private int $rows;
    private int $cols;
    private array $visited;
    private array $directions = [
        'up' => [-1, 0],
        'right' => [0, 1],
        'down' => [1, 0],
        'left' => [0, -1],
    ];
    private string $currentDirection = 'up';
    private array $position;

    public function __construct(string $filePath)
    {
        $this->loadMap($filePath);
        $this->findStartPosition();
    }

    private function loadMap(string $filePath): void
    {
        $file = file_get_contents($filePath);
        $this->map = array_map('str_split', explode(PHP_EOL, trim($file)));
        $this->rows = count($this->map);
        $this->cols = count($this->map[0]);
    }

    private function findStartPosition(): void
    {
        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->cols; $col++) {
                if ($this->map[$row][$col] === '^') {
                    $this->position = [$row, $col];
                    $this->visited[$row . ',' . $col] = true;
                    $this->map[$row][$col] = '.';
                    return;
                }
            }
        }
    }

    public function simulatePatrol(): int
    {

        $count = 0;
        $isPatrolling = true;

        while ($isPatrolling) {

            $nextLine = $this->position[0] + $this->directions[$this->currentDirection][0];
            $nextCol = $this->position[1] + $this->directions[$this->currentDirection][1];

            if (!$this->isValidPosition($nextLine, $nextCol)) {
                $isPatrolling = false;
                continue;
            }

            if ($this->isObstacle()) {
                $this->turnRight();
            } else {
                if (!$this->moveForward()) {
                    $isPatrolling = false;
                    continue;
                }
            }
            $count++;
        }

        echo "count: " . $count . PHP_EOL;

        echo "Visited: " . PHP_EOL;
        foreach (array_keys($this->visited) as $location) {
            echo $location . PHP_EOL;
        }


        print_r($this->visited);

        return count($this->visited);
    }

    private function isObstacle(): bool
    {
        $nextLine = $this->position[0] + $this->directions[$this->currentDirection][0];
        $nextCol = $this->position[1] + $this->directions[$this->currentDirection][1];


        return !$this->isValidPosition($nextLine, $nextCol) || $this->map[$nextLine][$nextCol] === '#';
    }

    private function isValidPosition(int $row, int $col): bool
    {
        return $row >= 0 && $row < $this->rows && $col >= 0 && $col < $this->cols;
    }
    private function turnRight(): void
    {
        $this->currentDirection = match ($this->currentDirection) {
            'up' => 'right',
            'right' => 'down',
            'down' => 'left',
            'left' => 'up',
        };
    }
    private function moveForward(): bool
    {
        $nextLine = $this->position[0] + $this->directions[$this->currentDirection][0];
        $nextCol = $this->position[1] + $this->directions[$this->currentDirection][1];

        if (!$this->isValidPosition($nextLine, $nextCol)) {
            return false;
        }

        $this->position = [$nextLine, $nextCol];
        $key = $nextLine . ',' . $nextCol;
        if (!isset($this->visited[$key])) {
            $this->visited[$key] = true;
        }
        return true;
    }
}


try {
    $guardPatrol = new GuardPatrol('day6ex.input');
    echo 'Distinct positions: ' . $guardPatrol->simulatePatrol();
} catch (Exception $e) {
    echo $e->getMessage();
}
