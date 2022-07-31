<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests;

use Forge\Data\Provider\Sort;
use PHPUnit\Framework\TestCase;

final class SortTest extends TestCase
{
    public function testAttributeOrders()
    {
        $sort = new Sort();

        $sort->attributes(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort(true);

        $sort->attributeOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $sort->getAttributeOrders());

        $sort->multiSort(false);

        $sort->attributeOrders(['age' => SORT_DESC, 'name' => SORT_ASC]);
        $this->assertSame(['age' => SORT_DESC], $sort->getAttributeOrders());

        $sort->attributeOrders(['age' => SORT_DESC, 'name' => SORT_ASC], false);
        $this->assertSame(['age' => SORT_DESC, 'name' => SORT_ASC], $sort->getAttributeOrders());

        $sort->attributeOrders(['unexistingAttribute' => SORT_ASC]);
        $this->assertSame([], $sort->getAttributeOrders());

        $sort->attributeOrders(['unexistingAttribute' => SORT_ASC], false);
        $this->assertSame(['unexistingAttribute' => SORT_ASC], $sort->getAttributeOrders());
    }

    public function testGetAttributeOrder()
    {
        $sort = new Sort();

        $sort->attributes(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->assertSame(SORT_ASC, $sort->getAttributeOrder('age'));
        $this->assertSame(SORT_DESC, $sort->getAttributeOrder('name'));
        $this->assertNull($sort->getAttributeOrder('xyz'));
    }

    public function testGetAttributeOrders()
    {
        $sort = new Sort();

        $sort->attributes(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
        )->params(['sort' => 'age,-name'])->multiSort();

        $orders = $sort->getAttributeOrders();
        $this->assertCount(2, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(SORT_DESC, $orders['name']);

        $sort->multiSort(false);

        $orders = $sort->getAttributeOrders(true);
        $this->assertCount(1, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/13260
     */
    public function testGetExpressionOrders()
    {
        $sort = new Sort();

        $sort->attributes(
            [
                'name' => [
                    'asc' => '[[last_name]] ASC NULLS FIRST',
                    'desc' => '[[last_name]] DESC NULLS LAST',
                ],
            ]
        );

        $sort->params(['sort' => '-name']);
        $orders = $sort->getOrders();
        $this->assertCount(1, $orders);
        $this->assertSame('[[last_name]] DESC NULLS LAST', $orders[0]);

        $sort->params(['sort' => 'name']);
        $orders = $sort->getOrders(true);
        $this->assertCount(1, $orders);
        $this->assertSame('[[last_name]] ASC NULLS FIRST', $orders[0]);
    }

    public function testGetOrders(): void
    {
        $sort = new Sort();

        $sort->attributes(
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

        $sort->multiSort(false);

        $orders = $sort->getAttributeOrders(true);

        $this->assertCount(1, $orders);
        $this->assertSame(SORT_ASC, $orders['age']);
        $this->assertSame(
            [
                'age' => [
                    'asc' => ['age' => SORT_ASC],
                    'desc' => ['age' => SORT_DESC],
                ],
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            $sort->getAttributes(),
        );
    }

    public function testGetSeparator(): void
    {
        $sort = new Sort();
        $this->assertSame(',', $sort->getSeparator());
    }

    public function testGetSortParams()
    {
        $sort = new Sort();
        $this->assertSame('sort', $sort->getSortParam());
    }

    public function testHasAttribute(): void
    {
        $sort = new Sort();

        $sort->attributes(
            [
                'name' => [
                    'asc' => '[[last_name]] ASC NULLS FIRST',
                    'desc' => '[[last_name]] DESC NULLS LAST',
                ],
            ]
        );

        $this->assertTrue($sort->hasAttribute('name'));
    }

    public function testIsMultisort(): void
    {
        $sort = new Sort();

        $sort->attributes(
            [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ]
        )->params(['sort' => 'age,-name'])->multiSort();

        $this->assertTrue($sort->isMultisort());
    }

    public function testSeparator(): void
    {
        $sort = new Sort();
        $sort->separator(';');
        $this->assertSame(';', $sort->getSeparator());
    }

    public function testSortParam(): void
    {
        $sort = new Sort();
        $sort->sortParam('order');
        $this->assertSame('order', $sort->getSortParam());
    }
}
