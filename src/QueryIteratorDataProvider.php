<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Throwable;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Query\QueryInterface;

/**
 * Provides a way to iterate over the results of {@see QueryInterface} in terms of data items.
 */
final class QueryIteratorDataProvider extends AbstractIteratorDataDataProvider
{
    public function __construct(private readonly QueryInterface $query)
    {
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function count(): int
    {
        return (int) $this->query->count();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function read(): array
    {
        return $this->query->limit($this->limit)->offset($this->offset)->all();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function readOne(): array
    {
        return $this->query->limit(1)->offset($this->offset)->all();
    }

    public function sortOrders(array $orders): self
    {
        $this->query->orderBy($orders);

        return $this;
    }
}
