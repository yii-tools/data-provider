<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Driver\Sqlite;

use Yii\DataProvider\Tests\Base\AbstractActiveDataProviderTest;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;

final class ActiveIteratorProviderTest extends AbstractActiveDataProviderTest
{
    protected function setUp(): void
    {
        $this->db = SqliteConnection::getConnection();

        parent::setUp();
    }
}
