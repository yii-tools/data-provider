<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use InvalidArgumentException;

use function array_merge;
use function explode;
use function implode;
use function is_array;
use function is_iterable;
use function str_starts_with;
use function substr;

/**
 * Represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several columns, we can use Sort to represent the sorting
 * information and generate appropriate hyperlinks that can lead to sort actions.
 *
 * A typical usage example is as follows.
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
    private array $columns = [];
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
     * The `age` column is a simple column which is equal to the following:
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
        $new = clone $this;

        /** @psalm-var array<string,array|string> $values */
        foreach ($values as $name => $column) {
            if (!is_array($column)) {
                $new->columns[$column] = ['asc' => [$column => SORT_ASC], 'desc' => [$column => SORT_DESC]];
            } else {
                $new->columns[$name] = $column;
            }
        }

        return $new;
    }

    /**
     * @param string $value The column name.
     *
     * @return int|null Sort direction of the column.
     * Can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     * `Null` is returned if the column is invalid or doesn't need to be sorted.
     */
    public function getColumnOrder(string $value): int|null
    {
        $orders = $this->getColumnOrders();

        return isset($orders[$value]) && is_int($orders[$value]) ? $orders[$value] : null;
    }

    /**
     * @return array Sort directions indexed by column names.
     * Sort direction can be either `SORT_ASC` for ascending order or `SORT_DESC` for descending order.
     */
    public function getColumnOrders(): array
    {
        /** @psalm-var string[] */
        $columns = $this->columns;

        if ($this->params === [] && $this->multiSort === false) {
            return array_slice($columns, 0, 1, true);
        }

        $columnOrders = [];

        foreach ($columns as $name => $ignored) {
            if (isset($this->params[$this->sortParamName])) {
                /** @psalm-var array<array-key,string> $sortParamName */
                $sortParamName = $this->parseSortParam((string) $this->params[$this->sortParamName]);
                foreach ($sortParamName as $column) {
                    $descending = str_starts_with($column, '-');
                    $column = $descending ? substr($column, 1) : $column;

                    if ($this->hasColumn($column)) {
                        $columnOrders[$column] = $descending ? SORT_DESC : SORT_ASC;

                        if ($this->multiSort === false) {
                            break;
                        }
                    }
                }
            } else {
                $columnOrders[$name] = SORT_ASC;
            }
        }

        return $columnOrders;
    }

    /**
     * @return array The columns (`keys`) and their corresponding sort directions (`values`).
     * This can be passed to construct a DB query.
     */
    public function getOrders(): array
    {
        $columns = [];
        $columnOrders = $this->getColumnOrders();

        /** @psalm-var array<string,int> $columnOrders */
        foreach ($columnOrders as $column => $direction) {
            /** @var array $definition */
            $definition = $this->hasColumn($column) ? $this->columns[$column] : [];
            /** @var array $values */
            $values = $definition[$direction === SORT_ASC ? 'asc' : 'desc'];
            /** @psalm-var array<string,int>|string $values */
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

        $directions = $this->getColumnOrders();
        $direction = isset($directions[$column]) && $directions[$column] === SORT_DESC ? SORT_ASC : SORT_DESC;

        unset($directions[$column]);

        $directions = match ($this->multiSort) {
            true => array_merge([$column => $direction], $directions),
            default => [$column => $direction],
        };

        $sorts = [];

        /** @psalm-var array<string, int> $directions */
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
        $columnOrders = $this->getColumnOrders();

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

            foreach ($directions as $attribute => $dir) {
                $sorts[] = $dir === SORT_DESC ? '-' . $attribute : $attribute;
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
     * @param bool $value Whether the sorting can be applied to many attributes simultaneously.
     *
     * Defaults to `false`, which means each time the data can only be sorted by one column.
     */
    public function multiSort(bool $value = true): self
    {
        $new = clone $this;
        $new->multiSort = $value;

        return $new;
    }

    /**
     * @param array $value parameters (name => value) that should be used to obtain the current sort directions and to
     * create new sort URLs. If not set, `$_GET` will be used instead.
     *
     * To add hash to all links, use `array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by {@see sortParamName} is considered to be the current sort directions.
     * If the element doesn't exist, the {@see defaultColumnOrder} will be used.
     *
     * @see sortParamName
     * @see defaultColumnOrder
     */
    public function params(array $value): self
    {
        $new = clone $this;
        $new->params = $value;

        return $new;
    }

    /**
     * @param string $value The character used to separate different attributes that need to be sorted by.
     */
    public function separator(string $value): self
    {
        $new = clone $this;
        $new->separator = $value ?: ',';

        return $new;
    }

    /**
     * @param string $value The column name of the parameter that specifies which attributes to be sorted in which
     * direction. Defaults to `sort`.
     *
     * @see params
     */
    public function sortParamName(string $value): self
    {
        $new = clone $this;
        $new->sortParamName = $value;

        return $new;
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
     * For example, the following return value will result in ascending sort by `category` and descending sort by
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
