<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Yiisoft\Db\Connection\ConnectionInterface;

use function count;

/**
 * Provides a way to iterate over the results of a SQL query with support for pagination.
 */
final class SQLIteratorDataDataProvider extends AbstractIteratorDataDataProvider
{
    public function __construct(private ConnectionInterface $db, private string $sql, private array $params = [])
    {
    }

    public function count(): int
    {
        return count($this->read());
    }

    public function read(): array
    {
        $sql = $this->db->getQueryBuilder()->buildOrderByAndLimit($this->sql, [], $this->limit, $this->offset);

        return $this->db->createCommand($sql, $this->params)->queryAll();
    }

    public function readOne(): array
    {
        return $this->withLimit(1)->read();
    }
}
