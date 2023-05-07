<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Traversable;

abstract class AbstractIteratorDataDataProvider implements IteratorDataProviderInterface
{
    protected int $limit = self::DEFAULT_LIMIT;
    protected int $offset = self::DEFAULT_OFFSET;

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

    public function withLimit(int $value): static
    {
        // validate limit
        if ($value < 0) {
            $value = self::DEFAULT_LIMIT;
        }

        $new = clone $this;
        $new->limit = $value;

        return $new;
    }

    public function withOffset(int $value): static
    {
        $new = clone $this;
        $new->offset = $value;

        // validate offset
        if ($new->limit * ($new->offset - 1) <= 0) {
            $new->offset = self::DEFAULT_OFFSET;
        }

        return $new;
    }
}
