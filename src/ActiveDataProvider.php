<?php

declare(strict_types=1);

namespace Forge\Data\Provider;

use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Strings\Inflector;

use function array_keys;
use function count;
use function is_string;

/**
 * ActiveDataProvider implements a data based on {@see ActiveQuery}.
 *
 * ActiveDataProvider provides data by performing DB queries using {@see ActiveQuery}.
 *
 * The following is an example of using ActiveDataProvider to provide ActiveRecord instances:
 *
 * ```php
 * $activeQuery = new ActiveQuery(MyClass::class, $db);
 * $data = new ActiveDataProvider($db, $activeQuery);
 * ```
 *
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ```php
 *    $activeQuery = new ActiveQuery(MyClass::class, $db);
 *
 *    $provider = new ActiveDataProvider(
 *        $activeQuery->from('order')->orderBy('id')
 *    );
 * ```
 */
final class ActiveDataProvider implements DataProviderInterface
{
    /**
     * @var callable|string the column that is used as the key of the data active record class.
     *
     * This can be either a column name, or a callable that returns the key value of a given data active record class.
     *
     * If this is not set, the following rules will be used to determine the keys of the data active record class:
     *
     * - If {@see query} is an {@see ActiveQuery} instance, the primary keys of {@see ActiveQuery::arClass} will be
     *   used.
     *
     * - Otherwise, the keys of the {@see ActiveRecord} array will be used.
     *
     * @see getKeys()
     */
    private $key = '';
    private Pagination|null $pagination = null;
    private Sort|null $sort = null;

    public function __construct(private ActiveQuery $activeQuery)
    {
    }

    public function getARClasses(): array
    {
        $activeQuery = $this->activeQuery;

        $pagination = $this->getPagination();
        $pagination->totalCount($this->getTotalCount());

        if ($pagination->getTotalCount() === 0) {
            return [];
        }

        $activeQuery->limit($pagination->getLimit())->offset($pagination->getOffset());
        $activeQuery->addOrderBy($this->getSort()->getOrders());

        return $activeQuery->all();
    }

    public function getCount(): int
    {
        return count($this->getARClasses());
    }

    public function getKeys(): array
    {
        $arClasses = $this->getARClasses();
        $keys = [];

        if (!empty($this->key)) {
            /** @psalm-var array[] $arClasses */
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

        $arClass = $this->activeQuery->getARInstance();
        $pks = $arClass->primaryKey();

        if (count($pks) === 1) {
            /** @var string */
            $pk = $pks[0];
            /** @psalm-var array[] $arClasses */
            foreach ($arClasses as $arClass) {
                /** @var string */
                $keys[] = $arClass[$pk];
            }
        } else {
            /** @psalm-var array[] $arClasses */
            foreach ($arClasses as $arClass) {
                $kk = [];
                /** @psalm-var string[] $pks */
                foreach ($pks as $pk) {
                    /** @var string */
                    $kk[$pk] = $arClass[$pk];
                }
                $keys[] = $kk;
            }
        }

        return $keys;
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

    public function getTotalCount(): int
    {
        $activeQuery = $this->activeQuery;

        return (int) $activeQuery->limit(-1)->offset(-1)->orderBy([])->count();
    }

    public function key(callable|string $value): void
    {
        $this->key = $value;
    }

    public function sortParams(array $sortParams = []): void
    {
        /** @var ActiveRecord $arClass */
        $arClass = $this->activeQuery->getARInstance();

        /** @psalm-var string[] $attributes */
        $attributes = array_keys($arClass->getAttributes());

        $sortAttribute = [];

        foreach ($attributes as $attribute) {
            $sortAttribute[$attribute] = [
                'asc' => [$attribute => SORT_ASC],
                'desc' => [$attribute => SORT_DESC],
                'label' => (new Inflector())->toHumanReadable($attribute),
            ];
        }

        $this->getSort()->attributes($sortAttribute)->params($sortParams)->multiSort();
    }
}
