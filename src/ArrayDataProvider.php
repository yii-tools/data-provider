<?php

declare(strict_types=1);

namespace Forge\Data\Provider;

use InvalidArgumentException;
use Yiisoft\Arrays\ArraySorter;

/**
 * ArrayDataProvider implements a data based on a data array.
 *
 * The {@see allData} property contains all data models that may be sorted and/or paginated.
 *
 * ArrayDataProvider will provide the data after sorting and/or pagination.
 *
 * You may configure the {@see sort} and {@see pagination} properties to customize the sorting and pagination behaviors.
 *
 * Elements in the {@see allData} array may be either objects (e.g. model objects) or associative arrays (e.g. query
 * results of DAO).
 *
 * Make sure to set the {@see key} property to the name of the field that uniquely identifies a data record or false if
 * you do not have such a field.
 *
 * Compared to {@see ActiveDataProvider}, ArrayDataProvider could be less efficient because it needs to have
 * {@see allData} ready.
 *
 * ArrayDataProvider may be used in the following way:
 *
 * ```php
 * $query = new Query($db);
 * $provider = (new ArrayDataProvider())->allData($query->from('post')->all());
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ```
 *
 * Note: if you want to use the sorting feature, you must configure the {@see sort} property so that the provider knows
 * which columns can be sorted.
 */
final class ArrayDataProvider implements DataProviderInterface
{
    private array $allData = [];
    /** @var callable|string */
    private $key = '';

    public function __construct(private Pagination|null $pagination = null, private Sort|null $sort = null)
    {
    }

    /**
     * Returns a new instance with the specified data.
     *
     * @param array $values the data that is not paginated or sorted. When pagination is enabled, this property usually
     * contains more elements.
     *
     * The array elements must use zero-based integer keys.
     *
     * @return self The data provider itself.
     */
    public function allData(array $values): self
    {
        $new = clone $this;
        $new->allData = $values;

        return $new;
    }

    public function getARClasses(): array
    {
        $arClass = $this->allData;

        if ($arClass === []) {
            return [];
        }

        $pagination = $this->getPagination();
        $pagination->totalCount($this->getCount());

        $arClass = $this->sortModels($arClass);

        return array_slice($arClass, $pagination->getOffset(), $pagination->getLimit(), true);
    }

    public function getCount(): int
    {
        return count($this->allData);
    }

    public function getKeys(): array
    {
        $arClasses = $this->getARClasses();

        if (!empty($this->key)) {
            $keys = [];
            /** @var array */
            foreach ($arClasses as $arClass) {
                if (is_string($this->key)) {
                    /** @var mixed */
                    $keys[] = $arClass[$this->key];
                } else {
                    /** @var mixed */
                    $keys[] = ($this->key)($arClass);
                }
            }

            return $keys;
        }

        return array_keys($arClasses);
    }

    public function getPagination(): Pagination
    {
        if ($this->pagination === null) {
            $this->pagination = new Pagination();
        }

        return $this->pagination;
    }

    public function getSort(): Sort
    {
        if ($this->sort === null) {
            $this->sort = new Sort();
        }

        return $this->sort;
    }

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
    public function key($key): self
    {
        if (!is_string($key) && !is_callable($key)) {
            throw new InvalidArgumentException('The property "key" must be of type "string" or "callable".');
        }

        $new = clone $this;
        $new->key = $key;

        return $new;
    }

    private function sortModels(array $arClasses): array
    {
        $orders = $this->getSort()->getOrders();

        /** @psalm-var array<array-key, string> */
        $keys = array_keys($orders);

        /** @psalm-var array<array-key, int> */
        $direction = array_values($orders);

        if ($orders !== []) {
            /** @psalm-var array[] $arClasses */
            ArraySorter::multisort($arClasses, $keys, $direction);
        }

        return $arClasses;
    }
}
