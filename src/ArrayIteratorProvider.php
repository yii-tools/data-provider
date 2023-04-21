<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

use function array_slice;
use function count;

/**
 * @implements IteratorAggregate<int, array>
 */
final class ArrayIteratorProvider implements IteratorAggregate, Countable
{
    private int|null $limit = 0;
    private int $offset = 0;

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

    public function read(): array
    {
        $offset = $this->offset >= 1 ? (int) $this->limit * ($this->offset - 1) : $this->offset;

        return array_slice($this->data, $offset, $this->limit);
    }

    public function readOne(): array
    {
        return array_slice($this->data, $this->offset, 1);
    }

    public function withLimit(int|null $limit): static
    {
        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    public function withOffset(int $offset): static
    {
        $new = clone $this;
        $new->offset = $offset;

        return $new;
    }
}
