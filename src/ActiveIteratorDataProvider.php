<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Yiisoft\ActiveRecord\ActiveQueryInterface;

/**
 * Provides a way to iterate over the results of an Active Query with support for pagination.
 */
final class ActiveIteratorDataProvider extends AbstractIteratorDataDataProvider
{
    public function __construct(private ActiveQueryInterface $activeQuery)
    {
    }

    public function count(): int
    {
        return (int) $this->activeQuery->count();
    }

    public function read(): array
    {
        return $this->activeQuery->limit($this->limit)->offset($this->offset)->all();
    }

    public function readOne(): array
    {
        return $this->activeQuery->limit(1)->offset($this->offset)->all();
    }
}
