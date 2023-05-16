<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use ArrayIterator;
use Traversable;

use function ceil;

/**
 * The basic implementation of the {@see teratorDataProviderInterface} and can be used as a base class for data
 * providers that retrieve data from an array or an iterator object.
 */
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

    /**
     * @return int The total number of pages.
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->count() / $this->limit);
    }

    public function withLimit(int $value): static
    {
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

        $new->offset = match ($value <= 0) {
            true => self::DEFAULT_OFFSET,
            default => $new->limit * ($value - 1),
        };

        return $new;
    }
}
