<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\SQLIteratorDataDataProvider;
use Yii\DataProvider\Tests\Base\AbstractIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;

final class SQLIteratorProviderTest extends AbstractIteratorDataProviderTest
{
    protected function setUp(): void
    {
        $this->db = SqliteConnection::getConnection();
        $this->createSchema();
        $this->iteratorProvider = new SQLIteratorDataDataProvider($this->db, 'SELECT * FROM {{%user}}');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->dropSchema();
        unset($this->db, $this->iteratorProvider);

        parent::tearDown();
    }
}
