<?php

declare(strict_types=1);

namespace Yii\DataProvider;

use Countable;
use IteratorAggregate;
use RuntimeException;

use function ceil;

/**
 * Pagination represents information relevant to pagination of data items.
 *
 * When data needs to be rendered in multiple pages, Pagination can be used to represent information such as
 * {@see totalCount|total item count}, {@see pageSize|page size}, {@see currentPage| current page}, etc.
 *
 * This information can be passed to {@see LinkPager|pagers} to render pagination buttons or links.
 *
 * @implements IteratorAggregate<int, array>
 */
final class OffsetPaginator implements Countable, IteratorAggregate
{
    public function __construct(private IteratorProviderInterface $iteratorProvider)
    {
    }

    /**
     * @return int The total number of data items.
     */
    public function count(): int
    {
        return $this->iteratorProvider->count();
    }

    public function getIterator(): IteratorAggregate
    {
        return $this->iteratorProvider;
    }

    /**
     * @return int The zero-based index of the current page.
     */
    public function getLimit(): int
    {
        return $this->iteratorProvider->getLimit();
    }

    /**
     * @return int The offset of the data.
     *
     * This may be used to set the OFFSET value for SQL statement for fetching the current page of data.
     */
    public function getOffset(): int
    {
        return $this->iteratorProvider->getOffset();
    }

    /**
     * @return int The total number of pages.
     */
    public function getTotalPages(): int
    {
        $totalCount = $this->iteratorProvider->withLimit(-1)->withOffset(-1)->count();

        return (int) ceil($totalCount / $this->iteratorProvider->getLimit());
    }

    /**
     * Return new instance specifying the page size.
     *
     * @param int $value The number of items on each page.
     */
    public function withLimit(int $value): self
    {
        if ($value < 1) {
            throw new RuntimeException('Page size should be at least 1.');
        }

        $new = clone $this;
        $new->iteratorProvider = $new->iteratorProvider->withLimit($value);

        return $new;
    }

    /**
     * Return new instance specifying the current page.
     *
     * @param int $currentPage The zero-based index of the current page.
     */
    public function withOffset(int $value): self
    {
        if ($value < 1) {
            throw new RuntimeException('Current page should be at least 1.');
        }

        $new = clone $this;
        $new->iteratorProvider = $new->iteratorProvider->withOffset($value);

        return $new;
    }
}
