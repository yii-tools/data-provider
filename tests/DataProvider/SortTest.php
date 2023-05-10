<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use PHPUnit\Framework\TestCase;
use Yii\DataProvider\Sort;

final class SortTest extends TestCase
{
    private Sort $sort;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sort = new Sort();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sort);
    }

    public function testDefaultColumnOrder(): void
    {
        $this->sort->defaultColumnOrder(['age' => SORT_DESC, 'name' => SORT_ASC]);

        $this->assertSame(SORT_DESC, $this->sort->getColumnOrder('age'));
        $this->assertSame(SORT_ASC, $this->sort->getColumnOrder('name'));
    }

    public function testColumnOrders()
    {
        $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort(true);

        $this->sort->columnOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $this->sort->getColumnOrders());

        $this->sort->multiSort(false);

        $this->sort->columnOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC], $this->sort->getColumnOrders());

        $this->sort->columnOrders(['age' => SORT_DESC, 'name' => SORT_ASC], false);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $this->sort->getColumnOrders());

        $this->sort->columnOrders(['unexistingAttribute' => SORT_ASC]);
        $this->assertSame([], $this->sort->getColumnOrders());

        $this->sort->columnOrders(['unexistingAttribute' => SORT_ASC], false);
        $this->assertSame(['unexistingAttribute' => SORT_ASC], $this->sort->getColumnOrders());
    }

    public function testGetColumnOrder()
    {
        $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->assertSame(SORT_ASC, $this->sort->getColumnOrder('age'));
        $this->assertSame(SORT_DESC, $this->sort->getColumnOrder('name'));
        $this->assertNull($this->sort->getColumnOrder('xyz'));
    }

    public function testGetColumnOrders()
    {
        $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
        )->params(['sort' => 'age,-name'])->multiSort();

        $orders = $this->sort->getColumnOrders();
        $this->assertCount(2, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_DESC, $orders['name']);

        $this->sort->multiSort(false);

        $orders = $this->sort->getColumnOrders(true);
        $this->assertCount(1, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/13260
     */
    public function testGetExpressionOrders()
    {
        $this->sort->columns(
            [
                'name' => [
                    'asc' => '[[last_name]] ASC NULLS FIRST',
                    'desc' => '[[last_name]] DESC NULLS LAST',
                ],
            ]
        )->params(['sort' => '-name']);

        $orders = $this->sort->getOrders();

        $this->assertCount(1, $orders);
        $this->assertSame('[[last_name]] DESC NULLS LAST', $orders[0]);

        $this->sort->params(['sort' => 'name']);
        $orders = $this->sort->getOrders(true);

        $this->assertCount(1, $orders);
        $this->assertSame('[[last_name]] ASC NULLS FIRST', $orders[0]);
    }

    public function testGetOrders(): void
    {
        $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $orders = $this->sort->getOrders();

        $this->assertCount(3, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_DESC, $orders['first_name']);
        $this->assertSame(SORT_DESC, $orders['last_name']);

        $this->sort->params(['sort' => '-age,name']);

        $orders = $this->sort->getOrders();

        $this->assertCount(3, $orders);
        $this->assertSame(SORT_DESC, $orders['age']);
        $this->assertSame(SORT_ASC, $orders['first_name']);
        $this->assertSame(SORT_ASC, $orders['last_name']);

        $this->sort->multiSort(false);
        $orders = $this->sort->getOrders(true);

        $this->assertCount(1, $orders);
        $this->assertSame(SORT_DESC, $orders['age']);
    }

    public function testGetSortParams(): void
    {
        $this->sort
            ->columns(
                [
                    'age',
                    'name' => [
                        'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                        'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                    ],
                ],
            )
            ->multiSort()
            ->params(['sort' => 'age,-name']);

        $this->assertSame(['sort' => '-age,-name'], $this->sort->getSortParams('age'));
        $this->assertSame(['sort' => 'name,age'], $this->sort->getSortParams('name'));

        $this->sort->multiSort(false);

        $this->assertSame(['sort' => '-age'], $this->sort->getSortParams('age'));

        $this->sort
            ->defaultColumnOrder(['age' => SORT_DESC, 'name' => SORT_ASC])
            ->multiSort()
            ->params(['sort' => 'age,name']);

        $this->assertSame(['sort' => '-age,name'], $this->sort->getSortParams('age'));
    }

    public function testGetSortParamsWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown attribute: unexistingAttribute');

        $this->sort->columns(
            [
                'age',
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->sort->getSortParams('unexistingAttribute');
    }

    public function testSeparator(): void
    {
        $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort(true);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_DESC], $this->sort->getColumnOrders());

        $this->sort->separator('|');
        $this->sort->params(['sort' => 'age|name']);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_ASC], $this->sort->getColumnOrders(true));
    }

    public function testSortParam(): void
    {
        $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['order' => 'age,-name'])->sortParam('order')->multiSort(true);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_DESC], $this->sort->getColumnOrders());
    }
}
