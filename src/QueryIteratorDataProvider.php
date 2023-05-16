<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Yiisoft\Db\Query\QueryInterface;

/**
 * Provides a way to iterate over the results of {@see \Yiisoft\Db\Query\QueryInterface} in terms of data items.
 */
final class QueryIteratorDataProvider extends AbstractIteratorDataDataProvider
{
    public function __construct(private QueryInterface $query)
    {
    }

    public function count(): int
    {
        return (int) $this->query->count('*');
    }

    public function read(): array
    {
        return $this->query->limit($this->limit)->offset($this->offset)->all();
    }

    public function readOne(): array
    {
        return $this->query->limit(1)->offset($this->offset)->all();
    }

    public function sortOrders(array $orders): static
    {
        $this->query->orderBy($orders);

        return $this;
    }
}
