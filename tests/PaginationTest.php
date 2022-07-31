<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests;

use Forge\Data\Provider\Pagination;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Forge\TestUtils\Assert;

final class PaginationTest extends TestCase
{
    public function testCurrentPage(): void
    {
        $pagination = new Pagination();
        $pagination->currentPage(1);
        $this->assertSame(1, $pagination->getCurrentPage());
    }

    public function testCurrentPageException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Current page should be at least 1');
        $pagination = new Pagination();
        $pagination->currentPage(0);
    }

    public function testGetTotalPages(): void
    {
        $pagination = new Pagination();
        $pagination->totalCount(10);
        $pagination->pageSize(3);
        $this->assertSame(4, $pagination->getTotalPages());
    }

    public function testPageParam(): void
    {
        $assert = new Assert();
        $pagination = new Pagination();
        $pagination->pageParam('pager');
        $this->assertSame('pager', $assert->inaccessibleProperty($pagination, 'pageParam'));
    }

    public function testPageSizeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Page size should be at least 1');
        $pagination = new Pagination();
        $pagination->pageSize(0);
    }

    public function testPageSize(): void
    {
        $pagination = new Pagination();
        $pagination->pageSize(1);
        $this->assertSame(1, $pagination->getPageSize());
    }

    public function testPageSizeParam(): void
    {
        $assert = new Assert();
        $pagination = new Pagination();
        $pagination->pageSizeParam('per-page');
        $this->assertSame('per-page', $assert->inaccessibleProperty($pagination, 'pageSizeParam'));
    }

    public function testTotalCount(): void
    {
        $pagination = new Pagination();
        $pagination->totalCount(1);
        $this->assertSame(1, $pagination->getTotalCount());
    }
}
