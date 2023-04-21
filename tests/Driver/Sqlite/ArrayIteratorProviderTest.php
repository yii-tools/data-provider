<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Driver\Sqlite;

use Yii\DataProvider\ArrayIteratorProvider;
use PHPUnit\Framework\TestCase;

final class ArrayIteratorProviderTest extends TestCase
{
    private array $data =  [
        ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
        ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
        ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
    ];

    public function testGetIterator(): void
    {
        $arrayIteratorProvider = (new ArrayIteratorProvider($this->data))->withLimit(10);

        $this->assertCount(3, $arrayIteratorProvider);
        $this->assertSame($this->data, iterator_to_array($arrayIteratorProvider));
    }

    public function testImmutable(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);

        $this->assertNotSame($arrayIteratorProvider, $arrayIteratorProvider->withLimit(1));
        $this->assertNotSame($arrayIteratorProvider, $arrayIteratorProvider->withOffset(1));
    }

    public function testRead(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $arrayIteratorProvider = $arrayIteratorProvider->withLimit(2)->read();

        $this->assertCount(2, $arrayIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $arrayIteratorProvider[0],
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $arrayIteratorProvider[1],
        );
    }

    public function testReadOne(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $arrayIteratorProvider = $arrayIteratorProvider->withLimit(1)->readOne();

        $this->assertCount(1, $arrayIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $arrayIteratorProvider[0],
        );
    }

    public function testWithOffset(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $arrayIteratorProvider = $arrayIteratorProvider->withLimit(2)->withOffset(1)->read();

        $this->assertCount(2, $arrayIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $arrayIteratorProvider[0],
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $arrayIteratorProvider[1],
        );

        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $arrayIteratorProvider = $arrayIteratorProvider->withLimit(2)->withOffset(2)->read();

        $this->assertCount(1, $arrayIteratorProvider);
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $arrayIteratorProvider[0],
        );
    }

    public function testWithLimitNull(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $arrayIteratorProvider = $arrayIteratorProvider->withLimit(null)->withOffset(1)->read();

        $this->assertCount(3, $arrayIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $arrayIteratorProvider[0],
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $arrayIteratorProvider[1],
        );
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $arrayIteratorProvider[2],
        );
    }
}
