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

    public function testDefaultFieldOrder(): void
    {
        $this->sort->defaultFieldOrder(['age' => SORT_DESC, 'name' => SORT_ASC]);

        $this->assertSame(SORT_DESC, $this->sort->getFieldOrder('age'));
        $this->assertSame(SORT_ASC, $this->sort->getFieldOrder('name'));
    }

    public function testFieldOrders()
    {
        $this->sort->fields(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort(true);

        $this->sort->fieldOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $this->sort->getFieldOrders());

        $this->sort->multiSort(false);

        $this->sort->fieldOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC], $this->sort->getFieldOrders());

        $this->sort->fieldOrders(['age' => SORT_DESC, 'name' => SORT_ASC], false);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $this->sort->getFieldOrders());

        $this->sort->fieldOrders(['unexistingAttribute' => SORT_ASC]);
        $this->assertSame([], $this->sort->getFieldOrders());

        $this->sort->fieldOrders(['unexistingAttribute' => SORT_ASC], false);
        $this->assertSame(['unexistingAttribute' => SORT_ASC], $this->sort->getFieldOrders());
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/13260
     */
    public function testGetExpressionOrders()
    {
        $this->sort->fields(
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

    public function testGetFieldOrder()
    {
        $this->sort->fields(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->assertSame(SORT_ASC, $this->sort->getFieldOrder('age'));
        $this->assertSame(SORT_DESC, $this->sort->getFieldOrder('name'));
        $this->assertNull($this->sort->getFieldOrder('xyz'));
    }

    public function testGetFieldOrders()
    {
        $this->sort->fields(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
        )->params(['sort' => 'age,-name'])->multiSort();

        $orders = $this->sort->getFieldOrders();
        $this->assertCount(2, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_DESC, $orders['name']);

        $this->sort->multiSort(false);

        $orders = $this->sort->getFieldOrders(true);
        $this->assertCount(1, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
    }

    public function testGetOrders(): void
    {
        $this->sort->fields(
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

    public function testSeparator(): void
    {
        $this->sort->fields(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort(true);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_DESC], $this->sort->getFieldOrders());

        $this->sort->separator('|');
        $this->sort->params(['sort' => 'age|name']);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_ASC], $this->sort->getFieldOrders(true));
    }

    public function testSortParam(): void
    {
        $this->sort->fields(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['order' => 'age,-name'])->sortParam('order')->multiSort(true);

        $this->assertSame(['age' => SORT_ASC, 'name' => SORT_DESC], $this->sort->getFieldOrders());
    }
}
