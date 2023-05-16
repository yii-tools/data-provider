<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Throwable;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;

/**
 * Provides a way to iterate over the results of an active query with support for pagination.
 */
final class ActiveIteratorDataProvider extends AbstractIteratorDataDataProvider
{
    public function __construct(private readonly ActiveQueryInterface $activeQuery)
    {
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function count(): int
    {
        return (int) $this->activeQuery->count();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function read(): array
    {
        return $this->activeQuery->limit($this->limit)->offset($this->offset)->all();
    }

    /**
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function readOne(): array
    {
        return $this->activeQuery->limit(1)->offset($this->offset)->all();
    }

    public function sortOrders(array $orders): self
    {
        $this->activeQuery->orderBy($orders);

        return $this;
    }
}
