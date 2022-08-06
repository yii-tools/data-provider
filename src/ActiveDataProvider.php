<?php

declare(strict_types=1);

namespace Forge\Data\Provider;

use InvalidArgumentException;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Data\Reader\Sort;

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
    private int $limit = 0;
    private int $offset = 0;
    private Sort|null $sort = null;

    public function __construct(private ActiveQuery $activeQuery)
    {
    }

    public function count(): int
    {
        $activeQuery = $this->activeQuery;

        return (int) $activeQuery->limit(-1)->offset(-1)->orderBy([])->count();
    }

    public function getKeys(): array
    {
        $keys = [];
        $readAll = $this->read();

        if (!empty($this->key)) {
            /** @psalm-var array[] $readAll */
            foreach ($readAll as $read) {
                if (is_string($this->key)) {
                    /** @var mixed */
                    $keys[] = $read[$this->key];
                } else {
                    /** @var mixed */
                    $keys[] = ($this->key)($read);
                }
            }

            return $keys;
        }

        $pks = $this->activeQuery->getARInstance()->primaryKey();

        if (count($pks) === 1) {
            /** @var string */
            $pk = $pks[0];

            /** @psalm-var array[] $readAll */
            foreach ($readAll as $read) {
                /** @var string */
                $keys[] = $read[$pk];
            }
        } else {
            /** @psalm-var array[] $readAll */
            foreach ($readAll as $read) {
                $kk = [];

                /** @psalm-var string[] $pks */
                foreach ($pks as $pk) {
                    /** @var string */
                    $kk[$pk] = $read[$pk];
                }

                $keys[] = $kk;
            }
        }

        return $keys;
    }

    public function getSort(): ?Sort
    {
        return $this->sort;
    }

    public function key(callable|string $value): self
    {
        $new = clone $this;
        $new->key = $value;

        return $new;
    }

    public function read(): array
    {
        $activeQuery = $this->activeQuery;
        $criteria = $this->sort?->getCriteria() ?? '';

        $activeQuery->limit($this->limit)->offset($this->offset);
        $activeQuery->addOrderBy($criteria);

        return $activeQuery->all();
    }

    public function readOne()
    {
        $activeQuery = $this->activeQuery;
        $criteria = $this->sort?->getCriteria() ?? '';

        $activeQuery->limit(1);
        $activeQuery->addOrderBy($criteria);

        return $activeQuery->all();
    }

    public function withSort(?Sort $sort): static
    {
        $new = clone $this;
        $new->sort = $sort;

        return $new;
    }

    public function sortParams(string $value = ''): static
    {
        /** @var ActiveRecord $arClass */
        $arClass = $this->activeQuery->getARInstance();

        /** @psalm-var string[] $attributes */
        $attributes = array_keys($arClass->getAttributes());

        $new = clone $this;
        $new->sort = Sort::only($attributes)->withOrderString($value);

        return $new;
    }

    public function withLimit(int $limit): static
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('The limit must not be less than 0.');
        }

        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    public function withOffset(int $offset): static
    {
        $new = clone $this;
        $new->offset = $offset;

        return $new;
    }
}
