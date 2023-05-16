<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Throwable;
use Yii\DataProvider\QueryIteratorDataProvider;
use Yii\DataProvider\Sort;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Query\Query;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class QueryIteratorProviderTest extends AbstractIteratorDataProviderTest
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        $this->db = SqliteConnection::getConnection();
        $this->createSchema();
        $this->iteratorProvider = new QueryIteratorDataProvider(
            (new Query($this->db))->select('*')->from(User::tableName()),
        );
        $this->sort = new Sort();

        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function tearDown(): void
    {
        $this->dropSchema();
        unset($this->db, $this->iteratorProvider);

        parent::tearDown();
    }
}
