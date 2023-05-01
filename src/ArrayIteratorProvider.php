<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Traversable;

use function array_slice;
use function count;

/**
 * Provides a way to iterate over an array with support for pagination.
 */
final class ArrayIteratorProvider implements IteratorProviderInterface
{
    private int $limit = self::DEFAULT_LIMIT;
    private int $offset = self::DEFAULT_OFFSET;

    public function __construct(private array $data)
    {
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->read());
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function read(): array
    {
        $offset = $this->offset >= 1 ? $this->limit * ($this->offset - 1) : $this->offset;

        return array_slice($this->data, $offset, $this->limit);
    }

    public function readOne(): array
    {
        return array_slice($this->data, $this->offset, 1);
    }

    public function withLimit(int $value): static
    {
        $new = clone $this;
        $new->limit = $value;

        return $new;
    }

    public function withOffset(int $value): static
    {
        $new = clone $this;
        $new->offset = $value;

        return $new;
    }
}
