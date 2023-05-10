<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\ActiveIteratorDataProvider;
use Yii\DataProvider\Sort;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;
use Yiisoft\ActiveRecord\ActiveQuery;

final class ActiveIteratorDataProviderTest extends AbstractIteratorDataProviderTest
{
    protected function setUp(): void
    {
        $this->db = SqliteConnection::getConnection();
        $this->createSchema();
        $this->iteratorProvider = new ActiveIteratorDataProvider(new ActiveQuery(User::class, $this->db));
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
