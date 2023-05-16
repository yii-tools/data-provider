<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\ArrayIteratorDataProvider;
use Yii\DataProvider\Sort;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ArrayIteratorDataProviderTest extends AbstractIteratorDataProviderTest
{
    /** @psalm-var array<array-key, array|object> */
    private array $data = [
        ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
        ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
        ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
    ];

    protected function setUp(): void
    {
        $this->iteratorProvider = new ArrayIteratorDataProvider($this->data);
        $this->sort = new Sort();

        parent::setUp();
    }
}
