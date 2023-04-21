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
 * Provides a way to iterate over an array with support for pagination.
 *
 * @implements IteratorAggregate<int, array>
 */
final class ArrayIteratorProvider implements IteratorAggregate, Countable
{
    private int|null $limit = 0;
    private int $offset = 0;

    public function __construct(private array $data)
    {
    }

    /**
     * Returns the number of items in the array.
     *
     * @return int The number of items in the array.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Returns an instance of ArrayIterator, which allows iteration over the data array.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->read());
    }

    /**
     * Returns a portion of the data array based on the current limit and offset.
     *
     * @return array The portion of the data array based on the current limit and offset.
     */
    public function read(): array
    {
        $offset = $this->offset >= 1 ? (int) $this->limit * ($this->offset - 1) : $this->offset;

        return array_slice($this->data, $offset, $this->limit);
    }

    /**
     * Returns the first item in the data array based on the current offset.
     *
     * @return array The first item in the data array based on the current offset.
     */
    public function readOne(): array
    {
        return array_slice($this->data, $this->offset, 1);
    }

    /**
     * Returns a new instance specifying the number of items to be returned per page.
     *
     * @param int|null $limit The number of items to be returned per page.
     */
    public function withLimit(int|null $limit): static
    {
        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    /**
     * Returns a new instance specifying the number of items to be skipped before starting to return items.
     *
     * @param int $offset The number of items to be skipped before starting to return items.
     */
    public function withOffset(int $offset): static
    {
        $new = clone $this;
        $new->offset = $offset;

        return $new;
    }
}
