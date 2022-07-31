<?php

declare(strict_types=1);

namespace Forge\Data\Provider;

/**
 * DataProviderInterface is the interface that must be implemented by data provider classes.
 *
 * Data providers are components that sort and paginate data.
 */
interface DataProviderInterface
{
    /**
     * Returns the data active record classes in the current page.
     *
     * @return array the list of data active record classes in the current page.
     */
    public function getARClasses(): array;

    /**
     * Returns the number of data active record classes in the current page.
     *
     * @return int the number of data active record classes in the current page.
     */
    public function getCount(): int;

    /**
     * Returns the key values associated with the data active record classes.
     *
     * @return array the list of key values corresponding to {@see arClasses}. Each data active record class in
     * {@see arClasses} is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys(): array;

    /**
     * @return Pagination pagination object.
     */
    public function getPagination(): Pagination;

    /**
     * @return Sort the sorting object.
     */
    public function getSort(): Sort;
}
