<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use function array_merge;
use function explode;
use function is_array;
use function strncmp;
use function substr;

/**
 * Sort represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several fields, we can use Sort to represent the sorting information
 * and generate appropriate hyperlinks that can lead to sort actions.
 *
 * A typical usage example is as follows,
 *
 * ```php
 * $sort = new Sort();
 * $sort->fields(
 *     [
 *         'age',
 *         'name' => [
 *             'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
 *             'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
 *         ],
 *     ],
 * )->params(['sort' => 'age,-name'])->multisort();
 * ```
 *
 * In the above, we declare two {@see fields} that support sorting: `name` and `age`.
 */
final class Sort
{
    private array $defaultFieldOrder = [];
    private array $fields = [];
    private array|null $fieldOrders = null;
    private bool $multisort = false;
    private array $params = [];
    /** @psalm-var non-empty-string */
    private string $separator = ',';
    private string $sortParam = 'sort';

    /**
     * @param array $values The order that should be used when the current request does not specify any order.
     *
     * The array keys are field names and the array values are the corresponding sort directions.
     *
     * For example:
     *
     * ```php
     * [
     *     'name' => SORT_ASC,
     *     'created_at' => SORT_DESC,
     * ]
     * ```
     *
     * @see fieldOrders
     */
    public function defaultFieldOrder(array $values): self
    {
        $this->defaultFieldOrder = $values;
        return $this;
    }

    /**
     * @param array $values List of fields that are allowed to be sorted.
     *
     * Its syntax can be described using the following example:
     *
     * ```php
     * [
     *     'age',
     *     'name' => [
     *         'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
     *         'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
     *         'default' => SORT_DESC,
     *         'label' => 'Name',
     *     ],
     * ]
     * ```
     *
     * In the above, two fields are declared: `age` and `name`.
     * The `age` field is a simple column which is equivalent to the following:
     *
     * ```php
     * [
     *     'age' => [
     *         'asc' => ['age' => SORT_ASC],
     *         'desc' => ['age' => SORT_DESC],
     *     ],
     *     'default' => SORT_ASC,
     *     'label' => 'age',
     * ]
     * ```
     *
     * ```php
     * 'name' => [
     *     'asc' => '[[last_name]] ASC NULLS FIRST', // PostgreSQL specific feature
     *     'desc' => '[[last_name]] DESC NULLS LAST',
     * ]
     * ```
     *
     * The `name` field is a composite column:
     *
     * - The `name` key represents the field name which will appear in the URLs leading to sort actions.
     * - The `asc` and `desc` elements specify how to sort by the field in ascending and descending orders,
     *   respectively. Their values represent the actual columns and the directions by which the data should be sorted
     *   by.
     * - The `default` element specifies by which direction the field should be sorted if it is not currently sorted
     *   (the default value is ascending order).
     * - The `label` element specifies what label should be used to create a sort link.
     *
     * Note that if the Sort object is already created, you can only use the full format to configure every field.
     * Each field must include these elements: `asc` and `desc`.
     */
    public function fields(array $values = []): self
    {
        /** @var array<string,array|string> $values */
        foreach ($values as $name => $field) {
            if (!is_array($field)) {
                $this->fields[$field] = ['asc' => [$field => SORT_ASC], 'desc' => [$field => SORT_DESC]];
            } else {
                $this->fields[$name] = array_merge(
                    ['asc' => [$name => SORT_ASC], 'desc' => [$name => SORT_DESC]],
                    $field,
                );
            }
        }

        return $this;
    }

    /**
     * Sets up the current sort information.
     *
     * @param array $values Sort directions indexed by field names.
     * Sort direction can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     * @param bool $validate Whether to validate given field orders against {@see fields}.
     * If validation is enabled incorrect entries will be removed.
     *
     * @see multiSort
     *
     * @psalm-param array<string,int> $values
     */
    public function fieldOrders(array $values = [], bool $validate = true): void
    {
        if ($validate === false) {
            $this->fieldOrders = $values;
            return;
        }

        $this->fieldOrders = [];

        foreach ($values as $field => $order) {
            if ($this->hasField($field)) {
                $this->fieldOrders[$field] = $order;

                if ($this->isMultiSort() === false) {
                    return;
                }
            }
        }
    }

    /**
     * Returns the sort direction of the specified field in the current request.
     *
     * @param string $vale The field name.
     *
     * @return int|null Sort direction of the field.
     * Can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     * `null` is returned if the field is invalid or does not need to be sorted.
     */
    public function getFieldOrder(string $value): int|null
    {
        /** @var array<array-key,int> */
        $orders = $this->getFieldOrders();

        return $orders[$value] ?? null;
    }

    /**
     * Returns the currently requested sort information.
     *
     * @param bool $recalculate Whether to recalculate the sort directions.
     *
     * @return array Sort directions indexed by field names.
     * Sort direction can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     */
    public function getFieldOrders(bool $recalculate = false): array
    {
        if ($this->fieldOrders === null || $recalculate) {
            $this->fieldOrders = [];

            if (isset($this->params[$this->sortParam])) {
                $sortParam = $this->parseSortParam((string) $this->params[$this->sortParam]);
                /** @var array<array-key,string> $sortParam */
                foreach ($sortParam as $field) {
                    $descending = false;

                    if (strncmp($field, '-', 1) === 0) {
                        $descending = true;
                        $field = substr($field, 1);
                    }

                    if ($this->hasField($field)) {
                        $this->fieldOrders[$field] = $descending ? SORT_DESC : SORT_ASC;

                        if ($this->isMultiSort() === false) {
                            return $this->fieldOrders;
                        }
                    }
                }
            }

            if (empty($this->fieldOrders) && !empty($this->defaultFieldOrder)) {
                $this->fieldOrders = $this->defaultFieldOrder;
            }
        }

        return $this->fieldOrders;
    }

    /**
     * Returns the columns and their corresponding sort directions.
     *
     * @param bool $recalculate whether to recalculate the sort directions.
     *
     * @return array The columns (`keys`) and their corresponding sort directions (`values`).
     * This can be passed to construct a DB query.
     */
    public function getOrders(bool $recalculate = false): array
    {
        $fields = [];
        $fieldOrders = $this->getFieldOrders($recalculate);

        /** @psalm-var array<string,int> $fieldOrders */
        foreach ($fieldOrders as $field => $direction) {
            /** @var array */
            $definition = $this->hasField($field) ? $this->fields[$field] : [];
            /** @var array */
            $columns = $definition[$direction === SORT_ASC ? 'asc' : 'desc'];
            /** @var array<string,int>|string $columns */
            if (is_iterable($columns)) {
                foreach ($columns as $name => $dir) {
                    $fields[$name] = $dir;
                }
            } else {
                $fields[] = $columns;
            }
        }

        return $fields;
    }

    /**
     * Returns a value indicating whether the sort definition supports sorting by the named field.
     *
     * @param string $value The field name.
     *
     * @return bool Whether the sort definition supports sorting by the named field.
     */
    public function hasField(string $value): bool
    {
        return isset($this->fields[$value]);
    }

    /**
     * @return bool Whether the sorting can be applied to multiple attributes simultaneously.
     */
    public function isMultiSort(): bool
    {
        return $this->multisort;
    }

    /**
     * @param bool $value Whether the sorting can be applied to multiple attributes simultaneously.
     *
     * Defaults to `false`, which means each time the data can only be sorted by one field.
     */
    public function multiSort(bool $value = true): self
    {
        $this->multisort = $value;

        return $this;
    }

    /**
     * @param array $value parameters (name => value) that should be used to obtain the current sort directions and to
     * create new sort URLs. If not set, `$_GET` will be used instead.
     *
     * In order to add hash to all links use `array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by {@see sortParam} is considered to be the current sort directions.
     * If the element does not exist, the {@see defaultFieldOrder} will be used.
     *
     * @see sortParam
     * @see defaultFieldOrder
     */
    public function params(array $value): self
    {
        $this->params = $value;

        return $this;
    }

    /**
     * @param string $value The character used to separate different attributes that need to be sorted by.
     */
    public function separator(string $value): self
    {
        $this->separator = $value ?: ',';

        return $this;
    }

    /**
     * @param string $value The field name of the parameter that specifies which attributes to be sorted in which
     * direction. Defaults to `sort`.
     *
     * @see params
     */
    public function sortParam(string $value): self
    {
        $this->sortParam = $value;

        return $this;
    }

    /**
     * Parses the value of {@see sortParam} into an array of sort field.
     *
     * The format must be the field name only for ascending or the field name prefixed with `-` for descending.
     *
     * For example the following return value will result in ascending sort by `category` and descending sort by
     * `created_at`:
     *
     * ```php
     * [
     *     'category',
     *     '-created_at'
     * ]
     * ```
     *
     * @param string $param the value of the {@see sortParam}.
     *
     * @return array The valid sort attributes.
     */
    private function parseSortParam(string $param): array
    {
        return explode($this->separator, $param);
    }
}
