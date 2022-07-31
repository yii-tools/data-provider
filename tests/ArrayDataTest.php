<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests;

use Forge\Data\Provider\ArrayData;
use Forge\Data\Provider\Sort;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ArrayDataTest extends TestCase
{
    private array $mixedArray = [
        'key1' => ['name' => 'zero'],
        9 => ['name' => 'one'],
        'key3' => ['name' => 'two'],
    ];
    private array $namedArray = [
        'key1' => ['name' => 'zero'],
        'key2' => ['name' => 'one'],
        'key3' => ['name' => 'two'],
    ];
    private array $nestedArray = [
        ['name' => ['first' => 'joe', 'last' => 'dow']],
        ['name' => ['first' => 'nikita', 'last' => 'femme']],
        ['name' => 'tow'],
    ];
    private array $simpleArray = [
        ['name' => 'zero'],
        ['name' => 'one'],
        ['name' => 'two'],
    ];

    public function testGetARClasses(): void
    {
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->allData($this->simpleArray);

        $this->assertSame($this->simpleArray, $dataProvider->getARClasses());
    }

    public function testGetARClassesWithEmptyArray(): void
    {
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->allData([]);

        $this->assertSame([], $dataProvider->getARClasses());
    }

    public function testGetCount(): void
    {
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->allData($this->simpleArray);

        $this->assertSame(3, $dataProvider->getCount());
    }

    public function testGetTotalCount(): void
    {
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->allData($this->simpleArray);
        $user = $dataProvider->getARClasses();

        $this->assertSame(3, $dataProvider->getPagination()->getTotalCount());
    }

    public function testKeyException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The property "key" must be of type "string" or "callable".');
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->key(['name']);
    }

    public function testGetKeys(): void
    {
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->allData($this->simpleArray);
        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame([0, 1], $dataProvider->getKeys());

        $dataProvider = $dataProvider->allData($this->namedArray);
        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame(['key1', 'key2'], $dataProvider->getKeys());

        $dataProvider = $dataProvider->allData($this->mixedArray);
        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame(['key1', 9], $dataProvider->getKeys());
    }

    public function testInmutable(): void
    {
        $dataProvider = new ArrayData();

        $this->assertNotSame($dataProvider, $dataProvider->allData($this->simpleArray));
        $this->assertNotSame($dataProvider, $dataProvider->key('name'));
    }

    public function testKey(): void
    {
        $dataProvider = new ArrayData();
        $dataProvider = $dataProvider->allData($this->simpleArray)->key('name');
        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame(['zero', 'one'], $dataProvider->getKeys());

        $dataProvider = $dataProvider
            ->allData($this->nestedArray)
            ->key(static fn ($arClass) => $arClass['name']['first']);
        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame(['joe', 'nikita'], $dataProvider->getKeys());
    }

    public function testSort(): void
    {
        $sort = new Sort();
        $sort
            ->attributes(
                [
                    'sort' => [
                        'asc' => ['name' => SORT_ASC],
                        'desc' => ['name' => SORT_DESC],
                    ],
                ],
            )
            ->defaultOrder(['sort' => SORT_ASC]);
        $dataProvider = new ArrayData(sort: $sort);
        $dataProvider = $dataProvider->allData($this->simpleArray);

        $this->assertSame([['name' => 'one'], ['name' => 'two'], ['name' => 'zero']], $dataProvider->getARClasses());
    }
}
