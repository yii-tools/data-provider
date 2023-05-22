<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yii\DataProvider\Sort;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
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
        $sort = $this->sort->defaultColumnOrder(['age' => SORT_DESC, 'name' => SORT_ASC]);

        $this->assertSame(SORT_DESC, $sort->getColumnOrder('age'));
        $this->assertSame(SORT_ASC, $sort->getColumnOrder('name'));
    }

    public function testColumnOrders(): void
    {
        $sort = $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $sort = $sort->columnOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $sort->getColumnOrders());

        $sort = $sort->multiSort(false);

        $sort = $sort->columnOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC], $sort->getColumnOrders());

        $sort = $sort->columnOrders(['age' => SORT_DESC, 'name' => SORT_ASC], false);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $sort->getColumnOrders());

        $sort = $sort->columnOrders(['unexistingAttribute' => SORT_ASC]);
        $this->assertSame([], $sort->getColumnOrders());

        $sort = $sort->columnOrders(['unexistingAttribute' => SORT_ASC], false);
        $this->assertSame(['unexistingAttribute' => SORT_ASC], $sort->getColumnOrders());
    }

    public function testGetColumnOrder(): void
    {
        $sort = $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->assertSame(SORT_ASC, $sort->getColumnOrder('age'));
        $this->assertSame(SORT_DESC, $sort->getColumnOrder('name'));
        $this->assertNull($sort->getColumnOrder('xyz'));
    }

    public function testGetColumnOrders(): void
    {
        $sort = $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
        )->params(['sort' => 'age,-name'])->multiSort();

        $orders = $sort->getColumnOrders();
        $this->assertCount(2, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_DESC, $orders['name']);

        $sort = $sort->multiSort(false);

        $orders = $sort->getColumnOrders(true);
        $this->assertCount(1, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
    }

    public function testGetColumnOrdersWithEmpty(): void
    {
        $sort = $this->sort->columns(['age', 'name'])->multiSort();

        $orders = $sort->getColumnOrders();
        $this->assertCount(2, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_ASC, $orders['name']);

        $sort = $sort->multiSort(false);

        $orders = $sort->getColumnOrders(true);
        $this->assertCount(1, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/13260
     */
    public function testGetExpressionOrders(): void
    {
        $sort = $this->sort->columns(
            [
                'name' => [
                    'asc' => '[[last_name]] ASC NULLS FIRST',
                    'desc' => '[[last_name]] DESC NULLS LAST',
                ],
            ]
        )->params(['sort' => '-name']);

        $orders = $sort->getOrders();

        $this->assertCount(1, $orders);
        $this->assertSame('[[last_name]] DESC NULLS LAST', $orders[0]);

        $sort = $sort->params(['sort' => 'name']);
        $orders = $sort->getOrders();

        $this->assertCount(1, $orders);
        $this->assertSame('[[last_name]] ASC NULLS FIRST', $orders[0]);
    }

    public function testGetOrders(): void
    {
        $sort = $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $orders = $sort->getOrders();

        $this->assertCount(3, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_DESC, $orders['first_name']);
        $this->assertSame(SORT_DESC, $orders['last_name']);

        $sort = $sort->params(['sort' => '-age,name']);

        $orders = $sort->getOrders();

        $this->assertCount(3, $orders);
        $this->assertSame(SORT_DESC, $orders['age']);
        $this->assertSame(SORT_ASC, $orders['first_name']);
        $this->assertSame(SORT_ASC, $orders['last_name']);

        $sort = $sort->multiSort(false);
        $orders = $sort->getOrders();

        $this->assertCount(1, $orders);
        $this->assertSame(SORT_DESC, $orders['age']);
    }

    public function testGetSortParam(): void
    {
        $sort = $this->sort
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

        $this->assertSame(['sort' => '-age,-name'], $sort->getSortParam('age'));
        $this->assertSame(['sort' => 'name,age'], $sort->getSortParam('name'));

        $sort = $sort->multiSort(false);

        $this->assertSame(['sort' => '-age'], $sort->getSortParam('age'));

        $sort = $sort
            ->defaultColumnOrder(['age' => SORT_DESC, 'name' => SORT_ASC])
            ->multiSort()
            ->params(['sort' => 'age,name']);

        $this->assertSame(['sort' => '-age,name'], $sort->getSortParam('age'));
    }

    public function testGetSortParamWithException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown attribute: unexistingAttribute');

        $sort = $this->sort->columns(
            [
                'age',
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $sort->getSortParam('unexistingAttribute');
    }

    public function testGetSortParams(): void
    {
        $sort = $this->sort
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

        $this->assertSame(
            [
                'age' => ['sort' => '-age,-name'],
                'name' => ['sort' => 'name,age'],
            ],
            $sort->getSortParams(),
        );

        $sort = $sort->multiSort(false);

        $this->assertSame(['age' => ['sort' => '-age']], $sort->getSortParams());

        $sort = $sort
            ->defaultColumnOrder(['age' => SORT_DESC, 'name' => SORT_ASC])
            ->multiSort()
            ->params(['sort' => 'age,name']);

        $this->assertSame(
            [
                'age' => ['sort' => '-age,name'],
                'name' => ['sort' => '-name,age'],
            ],
            $sort->getSortParams(),
        );
    }

    public function testSeparator(): void
    {
        $sort = $this->sort->columns(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_DESC], $sort->getColumnOrders());

        $sort = $sort->separator('|')->params(['sort' => 'age|name']);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_ASC], $sort->getColumnOrders(true));
    }

    public function testSortParamName(): void
    {
        $sort = $this->sort->sortParamName('order');

        $this->assertSame('order', $sort->getSortParamName());
    }
}
