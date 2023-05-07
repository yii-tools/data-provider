<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\ArrayIteratorDataProvider;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;

final class ArrayIteratorProviderTest extends AbstractIteratorDataProviderTest
{
    private array $data = [
        ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
        ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
        ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
    ];

    protected function setUp(): void
    {
        $this->iteratorProvider = new ArrayIteratorDataProvider($this->data);

        parent::setUp();
    }
}
