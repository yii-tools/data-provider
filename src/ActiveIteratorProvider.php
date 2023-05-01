<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Yii\Interface\LimitInterface;
use Yii\Interface\OffsetInterface;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

/**
 * Provides a way to iterate over the results of an Active Query with support for pagination.
 *
 * @implements IteratorAggregate<int, array>
 */
final class ActiveIteratorProvider implements IteratorAggregate, Countable, LimitInterface, OffsetInterface
{
    private int $limit = 0;
    private int $offset = 0;

    public function __construct(private ActiveQueryInterface $activeQuery)
    {
    }

    /**
     * Returns the total number of items in the query.
     *
     * @return int The total number of items in the query
     */
    public function count(): int
    {
        return (int) $this->activeQuery->count();
    }

    /**
     * Returns an instance of the ArrayIterator class for the current page of results.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->read());
    }

    /**
     * Returns an array with the results for the current page of the query.
     *
     * @return array An array with the results for the current page of the query
     */
    public function read(): array
    {
        $offset = $this->offset >= 1 ? $this->limit * ($this->offset - 1) : $this->offset;

        return $this->activeQuery->limit($this->limit)->offset($offset)->all();
    }

    /**
     * Returns an array with the result for the first item in the current page of the query.
     *
     * @return array An array with the result for the first item in the current page of the query
     */
    public function readOne(): array
    {
        return $this->activeQuery->limit(1)->offset($this->offset)->all();
    }

    /**
     * Returns a new instance specifying the number of items to be returned per page.
     *
     * @param int $limit The number of items to be returned per page.
     */
    public function withLimit(int $limit): static
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
