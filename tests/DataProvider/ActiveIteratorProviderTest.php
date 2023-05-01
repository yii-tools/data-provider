<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\DataProvider;

use Yii\DataProvider\Tests\Base\AbstractActiveIteratorDataProviderTest;
use Yii\DataProvider\Tests\Support\Helper\SqliteConnection;

final class ActiveIteratorProviderTest extends AbstractActiveIteratorDataProviderTest
{
    protected function setUp(): void
    {
        $this->db = SqliteConnection::getConnection();

        parent::setUp();
    }
}
