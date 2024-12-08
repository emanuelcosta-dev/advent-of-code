<?php

namespace AdventOfCode2024\Day7;

use RuntimeException;

class BridgeRepair
{

    private array $equations = [];

    public function __construct() {}

    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Input file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read input file: {$filePath}");
        }

        $instance = new self();

        foreach (explode(PHP_EOL, trim($content)) as $line) {
            if (empty($line)) continue;

            [$testValue, $numbers] = explode(': ', $line);
            $instance->equations[] = [
                'test_value' => (int)$testValue,
                'numbers' => array_map('intval', explode(' ', $numbers))
            ];
        }

        return $instance;
    }

    public function getTotalCalibrationResult(): int
    {
        $sum = 0;

        foreach ($this->equations as $equation) {
            if ($this->isValidEquation($equation)) {
                $sum += $equation['test_value'];
            }
        }

        return $sum;
    }

    private function isValidEquation(array $equation): bool
    {
        $numbers = $equation['numbers'];
        $testValue = $equation['test_value'];
        $operatorCount = count($numbers) - 1;

        $combinations = $this->allPossibleCombinations($operatorCount);


        foreach ($combinations as $operators) {
            $result = $numbers[0];
            $result = $this->validateExpression($numbers, $operators);
            if ($result === $testValue) {
                return true;
            }
        }

        return false;
    }

    private function validateExpression(array $numbers, array $operators): int
    {
        $result = $numbers[0];
        $currentNumber = $result;
        for ($i = 0; $i < count($operators); $i++) {
            if ($operators[$i] === '||') {
                $currentNumber = (int)($currentNumber . $numbers[$i + 1]);
            } else {
                $currentNumber = $this->applyOperator($currentNumber, $numbers[$i + 1], $operators[$i]);
            }
            $result = $currentNumber;
        }
        return $result;
    }

    private function applyOperator(int $num1, int $num2, string $operator): int
    {
        return match ($operator) {
            '+' => $num1 + $num2,
            '*' => $num1 * $num2,
            default => throw new RuntimeException("Invalid operator: {$operator}")
        };
    }

    private function allPossibleCombinations(int $length): array
    {
        $operators = ['+', '*', '||'];
        $combinations = [];
        $total = pow(count($operators), $length);

        for ($i = 0; $i < $total; $i++) {
            $combination = [];
            $num = $i;

            for ($j = 0; $j < $length; $j++) {
                $combination[] = $operators[$num % 3];
                $num = (int)($num / 3);
            }
            $combinations[] = $combination;
        }


        return $combinations;
    }
}

try {
    $bridgeRepair = BridgeRepair::fromFile('day7.txt');
    echo "Total calibration result: " . $bridgeRepair->getTotalCalibrationResult();
} catch (\Throwable $th) {
    echo "Error: " . $th->getMessage();
}
