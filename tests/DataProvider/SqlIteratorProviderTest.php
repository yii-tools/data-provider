<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\Tests\Base\AbstractSqlIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;

final class SqlIteratorProviderTest extends AbstractSqlIteratorDataProviderTest
{
    protected function setUp(): void
    {
        $this->db = SqliteConnection::getConnection();

        parent::setUp();
    }
}
