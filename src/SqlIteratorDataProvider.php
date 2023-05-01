<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Traversable;
use Yiisoft\Db\Connection\ConnectionInterface;

use function count;

/**
 * Provides a way to iterate over the results of a SQL query with support for pagination.
 */
final class SqlIteratorDataProvider implements IteratorProviderInterface
{
    private int $limit = self::DEFAULT_LIMIT;
    private int $offset = self::DEFAULT_OFFSET;

    public function __construct(private ConnectionInterface $db, private string $sql, private array $params = [])
    {
    }

    public function count(): int
    {
        return count($this->read());
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
        $sql = $this->db->getQueryBuilder()->buildOrderByAndLimit($this->sql, [], $this->limit, $offset);

        return $this->db->createCommand($sql, $this->params)->queryAll();
    }

    public function readOne(): array
    {
        return $this->withLimit(1)->read();
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
