<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests;

use Forge\Data\Provider\ActiveDataProvider;
use Forge\Data\Provider\Tests\Support\ActiveRecord\User;
use Forge\Data\Provider\Tests\Support\Helper\SqliteConnection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\ActiveRecord\ActiveQuery;

final class ExceptionTest extends TestCase
{
    public function testsCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The limit must not be less than 0.');
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $dataProvider->withLimit(-1);
    }
}
