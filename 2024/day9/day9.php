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

final class PerBlockDefragmenter implements DefragmentationMethodInterface
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
    $filesystem = new Filesystem(
        __DIR__ . '/day9.txt',
        new PerBlockDefragmenter()
    );

    echo $filesystem->defragment()->getChecksum() . PHP_EOL;
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}" . PHP_EOL;
    exit(1);
}
