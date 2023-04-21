<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

/**
 * @implements IteratorAggregate<int, array>
 */
final class ActiveIteratorProvider implements IteratorAggregate, Countable
{
    private int $limit = 0;
    private int $offset = 0;

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

    public function read(): array
    {
        $offset = $this->limit * ($this->offset - 1);

        if ($offset === -1) {
            $offset = $this->offset;
        }

        return $this->activeQuery->limit($this->limit)->offset($offset)->all();
    }

    public function readOne(): array
    {
        return $this->activeQuery->limit(1)->offset($this->offset)->all();
    }

    public function withLimit(int $limit): static
    {
        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    public function withOffset(int $offset): static
    {
        $new = clone $this;
        $new->offset = $offset;

        return $new;
    }
}
