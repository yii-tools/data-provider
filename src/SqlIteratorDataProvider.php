<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Yii\Interface\LimitInterface;
use Yii\Interface\OffsetInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

use function count;

/**
 * Provides a way to iterate over the results of a SQL query with support for pagination.
 *
 * @implements IteratorAggregate<int, array>
 */
final class SqlIteratorDataProvider implements IteratorAggregate, Countable, LimitInterface, OffsetInterface
{
    private int $limit = 0;
    private int $offset = 0;

    public function __construct(private ConnectionInterface $db, private string $sql, private array $params = [])
    {
    }

    /**
     * @return int The number of items in the result set.
     */
    public function count(): int
    {
        return count($this->read());
    }

    /**
     * Returns an instance of the ArrayIterator class for the current page of results.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->read());
    }

    /**
     * Returns an array of all items in the result set.
     *
     * @return array An array of all items in the result set.
     */
    public function read(): array
    {
        $offset = $this->offset >= 1 ? $this->limit * ($this->offset - 1) : $this->offset;
        $sql = $this->db->getQueryBuilder()->buildOrderByAndLimit($this->sql, [], $this->limit, $offset);

        return $this->db->createCommand($sql, $this->params)->queryAll();
    }

    /**
     * @return array A single item from the result set.
     */
    public function readOne(): array
    {
        return $this->withLimit(1)->read();
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
