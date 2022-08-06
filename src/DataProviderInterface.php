<?php

declare(strict_types=1);

namespace Forge\Data\Provider;

use InvalidArgumentException;
use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\SortableDataInterface;

/**
 * DataProviderInterface is the interface that must be implemented by data provider classes.
 *
 * Data providers are components that sort and paginate data.
 */
interface DataProviderInterface extends CountableDataInterface, OffsetableDataInterface, ReadableDataInterface, SortableDataInterface
{
    /**
     * Returns the key values associated with the data active record classes.
     *
     * @return array the list of key values corresponding to {@see arClasses}. Each data active record class in
     * {@see arClasses} is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys(): array;

    /**
     * Returns a new instance with the specified key.
     *
     * @param callable|string $key the column that is used as the key of the data.
     *
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the index of the data array will be used.
     *
     * @throws InvalidArgumentException
     *
     * {@see getKeys()}
     *
     * @return self The data provider itself.
     */
    public function key(callable|string $value): self;
}
