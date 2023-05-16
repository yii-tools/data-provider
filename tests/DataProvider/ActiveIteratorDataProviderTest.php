<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Throwable;
use Yii\DataProvider\ActiveIteratorDataProvider;
use Yii\DataProvider\Sort;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ActiveIteratorDataProviderTest extends AbstractIteratorDataProviderTest
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
        $this->iteratorProvider = new ActiveIteratorDataProvider(new ActiveQuery(User::class, $this->db));
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
