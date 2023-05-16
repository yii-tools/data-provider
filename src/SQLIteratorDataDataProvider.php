<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;

use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;

use function count;

/**
 * Provides a way to iterate over the results of an SQL query with support for pagination.
 */
final class SQLIteratorDataDataProvider extends AbstractIteratorDataDataProvider
{
    private array $orders = [];

    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly string $sql,
        private readonly array $params = []
    ) {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function count(): int
    {
        return count($this->read());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function read(): array
    {
        $sql = $this->db
            ->getQueryBuilder()
            ->buildOrderByAndLimit($this->sql, $this->orders, $this->limit, $this->offset);

        return $this->db->createCommand($sql, $this->params)->queryAll();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function readOne(): array
    {
        return $this->withLimit(1)->read();
    }

    public function sortOrders(array $orders): self
    {
        $this->orders = $orders;

        return $this;
    }
}
