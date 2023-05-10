<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\QueryIteratorDataProvider;
use Yii\DataProvider\Sort;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;
use Yiisoft\Db\Query\Query;

final class QueryIteratorProviderTest extends AbstractIteratorDataProviderTest
{
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

    protected function tearDown(): void
    {
        $this->dropSchema();
        unset($this->db, $this->iteratorProvider);

        parent::tearDown();
    }
}
