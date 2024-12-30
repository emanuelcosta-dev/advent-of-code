<?php

declare(strict_types=1);

namespace AdventOfCode2024\Day9;

interface DefragmentationMethodInterface
{
    public function defragment(FilesystemInterface $filesystem): void;
}

interface FilesystemInterface
{
    public function getFiles(): array;
    public function getFreeSpace(): array;
    public function getPartition(): array;
    public function getSector(int $index): ?SectorInterface;
    public function setSector(int $index, SectorInterface $sector): self;
    public function defragment(): self;
    public function getChecksum(): int;
}

interface SectorInterface {}

abstract class AbstractSector implements SectorInterface {}

final class FreeSpace extends AbstractSector {}

final class File extends AbstractSector
{
    public function __construct(private readonly int $id) {}

    public function getId(): int
    {
        return $this->id;
    }
}

final class BlockDefragmenter implements DefragmentationMethodInterface
{
    public function defragment(FilesystemInterface $filesystem): void
    {
        $free = $filesystem->getFreeSpace();
        $files = $filesystem->getFiles();

        while ($this->hasMultipleGaps($filesystem)) {
            $this->moveFileToFreeSpace($filesystem, $free, $files);
        }
    }

    private function hasMultipleGaps(FilesystemInterface $filesystem): bool
    {
        $gapCount = 0;
        $partition = $filesystem->getPartition();
        $partitionCount = count($partition);

        for ($i = 0; $i < ($partitionCount - 1); $i++) {
            if ($this->isGapBetweenSectors($filesystem, $i)) {
                $gapCount++;
                if ($gapCount > 1) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isGapBetweenSectors(FilesystemInterface $filesystem, int $index): bool
    {
        return $filesystem->getSector($index) instanceof File &&
            $filesystem->getSector($index + 1) instanceof FreeSpace;
    }

    private function moveFileToFreeSpace(
        FilesystemInterface $filesystem,
        array &$freeSpace,
        array &$files
    ): void {
        $freeData = reset($freeSpace);
        $freeIdx = key($freeSpace);
        unset($freeSpace[$freeIdx]);

        $fileData = end($files);
        $fileIdx = key($files);
        unset($files[$fileIdx]);

        $filesystem->setSector($freeIdx, $fileData);
        $filesystem->setSector($fileIdx, $freeData);
    }
}

final class WholeFileDefragmenter implements DefragmentationMethodInterface
{
    public function defragment(FilesystemInterface $filesystem): void
    {
        $partition = $filesystem->getPartition();
        $filePositions = [];

        // First, map file IDs to their starting positions
        foreach ($partition as $index => $sector) {
            if ($sector instanceof File) {
                $fileId = $sector->getId();
                if (!isset($filePositions[$fileId])) {
                    $filePositions[$fileId] = $index;
                }
            }
        }

        // Sort by file ID in descending order
        krsort($filePositions);

        // Process each file
        foreach ($filePositions as $fileId => $startIndex) {
            $fileSize = $this->getFileSize($partition, $startIndex);
            $freeSpaceSpan = $this->findLeftmostFreeSpaceSpan($filesystem, $startIndex, $fileSize);

            if ($freeSpaceSpan !== null && $freeSpaceSpan['start'] < $startIndex) {
                $this->moveFileToFreeSpace($filesystem, $startIndex, $freeSpaceSpan, $fileSize);
            }
        }
    }

    private function findLeftmostFreeSpaceSpan(
        FilesystemInterface $filesystem,
        int $fileIndex,
        int $requiredSize
    ): ?array {
        $partition = $filesystem->getPartition();
        $currentSpanStart = null;
        $currentSpanSize = 0;

        // Look for free space spans before the current file
        for ($i = 0; $i < $fileIndex; $i++) {
            if ($partition[$i] instanceof FreeSpace) {
                if ($currentSpanStart === null) {
                    $currentSpanStart = $i;
                }
                $currentSpanSize++;

                if ($currentSpanSize >= $requiredSize) {
                    return [
                        'start' => $currentSpanStart,
                        'size' => $requiredSize
                    ];
                }
            } else {
                $currentSpanStart = null;
                $currentSpanSize = 0;
            }
        }

        return null;
    }

    private function moveFileToFreeSpace(
        FilesystemInterface $filesystem,
        int $fileIndex,
        array $freeSpaceSpan,
        int $fileSize
    ): void {
        $file = $filesystem->getSector($fileIndex);

        // Move file to free space
        for ($i = 0; $i < $fileSize; $i++) {
            $filesystem->setSector($freeSpaceSpan['start'] + $i, $file);
            $filesystem->setSector($fileIndex + $i, new FreeSpace());
        }
    }

    private function getFileSize(array $partition, int $startIndex): int
    {
        if (!($partition[$startIndex] instanceof File)) {
            return 0;
        }

        $size = 0;
        $fileId = $partition[$startIndex]->getId();

        for ($i = $startIndex; $i < count($partition); $i++) {
            if ($partition[$i] instanceof File && $partition[$i]->getId() === $fileId) {
                $size++;
            } else {
                break;
            }
        }

        return $size;
    }
}

final class Filesystem implements FilesystemInterface
{
    private array $partition = [];

    public function __construct(
        string $filename,
        private readonly DefragmentationMethodInterface $defragmenter
    ) {
        $this->buildPartition($filename);
    }

    private function buildPartition(string $filename): void
    {
        $disk = str_split(file_get_contents($filename));
        $diskLength = count($disk);
        $isFileLength = false;
        $fileId = -1;

        for ($i = 0; $i < $diskLength; $i++) {
            $isFileLength = !$isFileLength;
            $fileId += (int) $isFileLength;
            $value = (int) $disk[$i];

            for ($j = 0; $j < $value; $j++) {
                $this->partition[] = $isFileLength ? new File($fileId) : new FreeSpace();
            }
        }
    }

    public function getFiles(): array
    {
        return array_filter(
            $this->partition,
            fn(SectorInterface $sector): bool => $sector instanceof File
        );
    }

    public function getFreeSpace(): array
    {
        return array_filter(
            $this->partition,
            fn(SectorInterface $sector): bool => $sector instanceof FreeSpace
        );
    }

    public function getPartition(): array
    {
        return $this->partition;
    }

    public function getSector(int $index): ?SectorInterface
    {
        return $this->partition[$index] ?? null;
    }

    public function setSector(int $index, SectorInterface $sector): self
    {
        $this->partition[$index] = $sector;
        return $this;
    }

    public function defragment(): self
    {
        $this->defragmenter->defragment($this);
        return $this;
    }

    public function getChecksum(): int
    {
        return array_reduce(
            array_keys($this->getFiles()),
            fn(int $sum, int $index): int => $sum + ($index * $this->partition[$index]->getId()),
            0
        );
    }
}

try {
    // Part 1
    $filesystem1 = new Filesystem(
        __DIR__ . '/day9.txt',
        new BlockDefragmenter()
    );
    echo "Part 1: " . $filesystem1->defragment()->getChecksum() . PHP_EOL;

    // Part 2
    $filesystem2 = new Filesystem(
        __DIR__ . '/day9.txt',
        new WholeFileDefragmenter()
    );
    echo "Part 2: " . $filesystem2->defragment()->getChecksum() . PHP_EOL;
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}" . PHP_EOL;
    exit(1);
}
