<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Yiisoft\Db\Query\QueryInterface;

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
}
