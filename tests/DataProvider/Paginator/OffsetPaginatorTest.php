<?php

declare(strict_types=1);

namespace Yii\DataProvider\DataProvider\Paginator;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yii\DataProvider\ArrayIteratorProvider;
use Yii\DataProvider\OffsetPaginator;

final class OffsetPaginatorTest extends TestCase
{
    private array $data = [
        ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
        ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
        ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
    ];

    public function testCount(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);

        $this->assertSame(3, $offsetPaginator->count());
    }

    public function testGetIterator(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);

        $this->assertSame($arrayIteratorProvider, $offsetPaginator->getIterator());
    }

    public function testGetLimit(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);

        $this->assertSame(10, $offsetPaginator->getLimit());
    }

    public function testGetOffset(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);

        $this->assertSame(0, $offsetPaginator->getOffset());
    }

    public function testGetTotalPages(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);

        $this->assertSame(1, $offsetPaginator->getTotalPages());
    }

    public function testWithLimit(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);
        $newOffsetPaginator = $offsetPaginator->withLimit(5);

        $this->assertNotSame($offsetPaginator, $newOffsetPaginator);
        $this->assertSame(10, $offsetPaginator->getLimit());
        $this->assertSame(5, $newOffsetPaginator->getLimit());
    }

    public function testWithLimitException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Page size should be at least 1.');

        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);
        $offsetPaginator->withLimit(0);
    }

    public function testWithOffset(): void
    {
        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);
        $newOffsetPaginator = $offsetPaginator->withOffset(5);

        $this->assertNotSame($offsetPaginator, $newOffsetPaginator);
        $this->assertSame(0, $offsetPaginator->getOffset());
        $this->assertSame(5, $newOffsetPaginator->getOffset());
    }

    public function testWithOffsetException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Current page should be at least 1.');

        $arrayIteratorProvider = new ArrayIteratorProvider($this->data);
        $offsetPaginator = new OffsetPaginator($arrayIteratorProvider);
        $offsetPaginator->withOffset(0);
    }
}
