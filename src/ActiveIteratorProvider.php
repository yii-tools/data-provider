<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Traversable;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

/**
 * Provides a way to iterate over the results of an Active Query with support for pagination.
 */
final class ActiveIteratorProvider implements IteratorProviderInterface
{
    private int $limit = self::DEFAULT_LIMIT;
    private int $offset = self::DEFAULT_OFFSET;

    public function __construct(private ActiveQueryInterface $activeQuery)
    {
    }

    public function count(): int
    {
        return (int) $this->activeQuery->count();
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

        return $this->activeQuery->limit($this->limit)->offset($offset)->all();
    }

    public function readOne(): array
    {
        return $this->activeQuery->limit(1)->offset($this->offset)->all();
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
