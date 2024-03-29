<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Provides a way to iterate over the results of a query with support for pagination.
 *
 * @extends IteratorAggregate<int, array>
 */
interface IteratorDataProviderInterface extends Countable, IteratorAggregate
{
    /**
     * The default page size.
     */
    public const DEFAULT_LIMIT = 10;

    /**
     * The default offset.
     */
    public const DEFAULT_OFFSET = 0;

    /**
     * Returns the number of items in the data provider.
     *
     * @return int The number of items in the data provider.
     */
    public function count(): int;

    /**
     * Returns the limit of the data.
     *
     * This may be used to set the LIMIT value for SQL statement for fetching the current page of data.
     *
     * @return int The limit of the data.
     */
    public function getLimit(): int;

    /**
     * Returns the offset of the data.
     *
     * This may be used to set the OFFSET value for SQL statement for fetching the current page of data.
     *
     * @return int The offset of the data.
     */
    public function getOffset(): int;

    /**
     * Returns an instance of Traversable, which allows iteration over the data provider.
     */
    public function getIterator(): Traversable;

    /**
     * Returns the total number of pages.
     */
    public function getTotalPages(): int;

    /**
     * Returns a part of the data provider based on the current limit and offset.
     *
     * @return array The part of the data provider based on the current limit and offset.
     */
    public function read(): array;

    /**
     * Returns the first item in the data provider based on the current offset.
     *
     * @return array The first item in the data provider based on the current offset.
     */
    public function readOne(): array;

    /**
     * Sorts the data array by the given orders.
     *
     * @param array $orders An associative array where the keys represent the fields to sort by and the values represent
     * the sorting direction.
     */
    public function sortOrders(array $orders): self;

    /**
     * Returns a new instance specifying the number of items to be returned per page.
     *
     * @param int $value The number of items to be returned per page.
     */
    public function withLimit(int $value): static;

    /**
     * Returns a new instance specifying the number of items to be skipped before starting to return items.
     *
     * @param int $value The number of items to be skipped before starting to return items.
     */
    public function withOffset(int $value): static;
}
