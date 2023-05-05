<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use function array_slice;
use function count;

/**
 * Provides a way to iterate over an array with support for pagination.
 */
final class ArrayIteratorProvider extends AbstractIteratorProvider
{
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
}
