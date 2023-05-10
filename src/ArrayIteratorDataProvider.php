<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Yiisoft\Arrays\ArraySorter;

use function array_keys;
use function array_values;
use function array_slice;
use function count;

/**
 * Provides a way to iterate over an array with support for pagination.
 */
final class ArrayIteratorDataProvider extends AbstractIteratorDataDataProvider
{
    /**
     * @psalm-param array<array-key, array<array-key, mixed>|object> $data
     */
    public function __construct(private array $data)
    {
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function read(): array
    {
        return array_slice($this->data, $this->offset, $this->limit);
    }

    public function readOne(): array
    {
        return array_slice($this->data, $this->offset, 1);
    }

    public function sortOrders(array $orders): static
    {
        /** @psalm-var array<array-key, string> */
        $keys = array_keys($orders);

        /** @psalm-var array<array-key, int> */
        $direction = array_values($orders);

        if ($orders !== []) {
            ArraySorter::multisort($this->data, $keys, $direction);
        }

        return $this;
    }
}
