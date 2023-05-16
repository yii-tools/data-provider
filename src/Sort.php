<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use InvalidArgumentException;

use function array_merge;
use function explode;
use function implode;
use function is_array;
use function is_iterable;
use function strncmp;
use function substr;

/**
 * Represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several columns, we can use Sort to represent the sorting
 * information and generate appropriate hyperlinks that can lead to sort actions.
 *
 * A typical usage example is as follows,
 *
 * ```php
 * $sort = new Sort();
 * $sort->columns(
 *     [
 *         'age',
 *         'name' => [
 *             'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
 *             'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
 *         ],
 *     ],
 * )->params(['sort' => 'age,-name'])->multiSort();
 * ```
 *
 * In the above, we declare two {@see columns} that support sorting: `name` and `age`.
 */
final class Sort
{
    private array $defaultColumnOrder = [];
    private array $columns = [];
    private array|null $columnOrders = null;
    private bool $multiSort = false;
    private array $params = [];
    /** @psalm-var non-empty-string */
    private string $separator = ',';
    private string $sortParamName = 'sort';

    /**
     * @param array $values List of columns that are allowed to be sorted.
     *
     * Its syntax can be described using the following example:
     *
     * ```php
     * [
     *     'age',
     *     'name' => [
     *         'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
     *         'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
     *     ],
     * ]
     * ```
     *
     * In the above, two columns are declared: `age` and `name`.
     * The `age` column is a simple column which is equivalent to the following:
     *
     * ```php
     * [
     *     'age' => [
     *         'asc' => ['age' => SORT_ASC],
     *         'desc' => ['age' => SORT_DESC],
     *     ],
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
     * The `name` column is a composite column:
     *
     * - The `name` key represents the column name which will appear in the URLs leading to sort actions.
     * - The `asc` and `desc` elements specify how to sort by the column in ascending and descending orders,
     *   respectively. Their values represent the actual columns and the directions by which the data should be sorted
     *   by.
     */
    public function columns(array $values = []): self
    {
        /** @var array<string,array|string> $values */
        foreach ($values as $name => $column) {
            if (!is_array($column)) {
                $this->columns[$column] = ['asc' => [$column => SORT_ASC], 'desc' => [$column => SORT_DESC]];
            } else {
                $this->columns[$name] = $column;
            }
        }

        return $this;
    }

    /**
     * Sets up the current sort information.
     *
     * @param array $values Sort directions indexed by column names.
     * Sort direction can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     * @param bool $validate Whether to validate given column orders against {@see columns}.
     * If validation is enabled incorrect entries will be removed.
     *
     * @see multiSort
     *
     * @psalm-param array<string,int> $values
     */
    public function columnOrders(array $values = [], bool $validate = true): void
    {
        if ($validate === false) {
            $this->columnOrders = $values;

            return;
        }

        $this->columnOrders = [];

        foreach ($values as $column => $order) {
            if ($this->hasColumn($column)) {
                $this->columnOrders[$column] = $order;

                if ($this->multiSort === false) {
                    return;
                }
            }
        }
    }

    /**
     * @param array $values The order that should be used when the current request does not specify any order.
     *
     * The array keys are column names and the array values are the corresponding sort directions.
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
     * @see columnOrders
     */
    public function defaultColumnOrder(array $values): self
    {
        $this->defaultColumnOrder = $values;

        return $this;
    }

    /**
     * @param string $vale The column name.
     *
     * @return int|null Sort direction of the column.
     * Can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     * `null` is returned if the column is invalid or does not need to be sorted.
     */
    public function getColumnOrder(string $value): int|null
    {
        /** @var array<array-key,int> */
        $orders = $this->getColumnOrders();

        return $orders[$value] ?? null;
    }

    /**
     * @param bool $value Whether to recalculate the sort directions.
     *
     * @return array Sort directions indexed by column names.
     * Sort direction can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     */
    public function getColumnOrders(bool $value = false): array
    {
        if ($value === false && $this->columnOrders !== null) {
            return $this->columnOrders;
        }

        if (isset($this->params[$this->sortParamName])) {
            $this->columnOrders = [];

            $sortParamName = $this->parseSortParam((string) $this->params[$this->sortParamName]);

            /** @var array<array-key,string> $sortParamName */
            foreach ($sortParamName as $column) {
                $descending = strncmp($column, '-', 1) === 0;
                $column = $descending ? substr($column, 1) : $column;

                if ($this->hasColumn($column)) {
                    $this->columnOrders[$column] = $descending ? SORT_DESC : SORT_ASC;

                    if (!$this->multiSort) {
                        break;
                    }
                }
            }
        } else {
            $this->columnOrders = $this->defaultColumnOrder;
        }

        return $this->columnOrders;
    }

    /**
     * @param bool $value whether to recalculate the sort directions. Defaults to `false`.
     *
     * @return array The columns (`keys`) and their corresponding sort directions (`values`).
     * This can be passed to construct a DB query.
     */
    public function getOrders(): array
    {
        $columns = [];
        $columnOrders = $this->getColumnOrders(true);

        /** @psalm-var array<string,int> $columnOrders */
        foreach ($columnOrders as $column => $direction) {
            /** @var array */
            $definition = $this->hasColumn($column) ? $this->columns[$column] : [];
            /** @var array */
            $values = $definition[$direction === SORT_ASC ? 'asc' : 'desc'];
            /** @var array<string,int>|string $values */
            if (is_iterable($values)) {
                foreach ($values as $name => $dir) {
                    $columns[$name] = $dir;
                }
            } else {
                $columns[] = $values;
            }
        }

        return $columns;
    }

    /**
     * @param string $column The name of the column.
     *
     * @throws InvalidArgumentException if the specified column is unknown.
     *
     * @return array The sort parameter value for the specified column.
     */
    public function getSortParam(string $column): array
    {
        if ($this->hasColumn($column) === false) {
            throw new InvalidArgumentException("Unknown attribute: $column");
        }

        $directions = $this->getColumnOrders(true);

        $direction = isset($directions[$column]) && $directions[$column] === SORT_DESC ? SORT_ASC : SORT_DESC;
        unset($directions[$column]);

        $directions = match ($this->multiSort) {
            true => array_merge([$column => $direction], $directions),
            default => [$column => $direction],
        };

        $sorts = [];

        /** @var array<string, int> $directions */
        foreach ($directions as $attribute => $direction) {
            $sorts[] = $direction === SORT_DESC ? '-' . $attribute : $attribute;
        }

        return [$this->sortParamName => implode($this->separator, $sorts)];
    }

    /**
     * Returns an array of sort parameter values for all columns.
     *
     * @return array An array of sort parameter values for all columns.
     */
    public function getSortParams(): array
    {
        $sortParams = [];
        $columnOrders = $this->getColumnOrders(true);

        /** @psalm-var array<string,int> $columnOrders */
        foreach ($columnOrders as $column => $direction) {
            $directions = $columnOrders;
            $direction = $direction === SORT_DESC ? SORT_ASC : SORT_DESC;
            unset($directions[$column]);

            $directions = match ($this->multiSort) {
                true => array_merge([$column => $direction], $directions),
                default => [$column => $direction],
            };

            $sorts = [];

            foreach ($directions as $attribute => $direction) {
                $sorts[] = $direction === SORT_DESC ? '-' . $attribute : $attribute;
            }

            $sortParams[$column][$this->sortParamName] = implode($this->separator, $sorts);
        }

        return $sortParams;
    }

    /**
     * @return string The parameter name for specifying sort information in a URL.
     */
    public function getSortParamName(): string
    {
        return $this->sortParamName;
    }

    /**
     * @param bool $value Whether the sorting can be applied to multiple attributes simultaneously.
     *
     * Defaults to `false`, which means each time the data can only be sorted by one column.
     */
    public function multiSort(bool $value = true): self
    {
        $this->multiSort = $value;

        return $this;
    }

    /**
     * @param array $value parameters (name => value) that should be used to obtain the current sort directions and to
     * create new sort URLs. If not set, `$_GET` will be used instead.
     *
     * In order to add hash to all links use `\array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by {@see sortParamName} is considered to be the current sort directions.
     * If the element does not exist, the {@see defaultColumnOrder} will be used.
     *
     * @see sortParamName
     * @see defaultColumnOrder
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
     * @param string $value The column name of the parameter that specifies which attributes to be sorted in which
     * direction. Defaults to `sort`.
     *
     * @see params
     */
    public function sortParamName(string $value): self
    {
        $this->sortParamName = $value;

        return $this;
    }

    /**
     * Returns a value indicating whether the sort definition supports sorting by the named column.
     *
     * @param string $value The column name.
     *
     * @return bool Whether the sort definition supports sorting by the named column.
     */
    private function hasColumn(string $value): bool
    {
        return isset($this->columns[$value]);
    }

    /**
     * Parses the value of {@see sortParamName} into an array of sort column.
     *
     * The format must be the column name only for ascending or the column name prefixed with `-` for descending.
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
     * @param string $param the value of the {@see sortParamName}.
     *
     * @return array The valid sort attributes.
     */
    private function parseSortParam(string $param): array
    {
        return explode($this->separator, $param);
    }
}
