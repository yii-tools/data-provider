<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests;

use Forge\Data\Provider\ActiveData;
use Forge\Data\Provider\Sort;
use Forge\Data\Provider\Tests\Support\ActiveRecord\User;
use Forge\Data\Provider\Tests\Support\Helper\SqliteConnection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yiisoft\ActiveRecord\ActiveQuery;

final class ActiveDataTest extends TestCase
{
    public function testGetARClasses(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveData($userQuery);
        $user = $dataProvider->getARClasses();

        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $user[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $user[1]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $user[2]->getAttributes(),
        );
    }

    public function testsCount(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveData($userQuery);

        $this->assertSame(3, $dataProvider->getCount());
    }

    public function testGetKeys(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveData($userQuery);

        $this->assertSame([1, 2, 3], $dataProvider->getKeys());

        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame([1, 2], $dataProvider->getKeys());

        $dataProvider->getPagination()->pageSize(2);
        $dataProvider->getPagination()->currentPage(2);

        $this->assertSame([3], $dataProvider->getKeys());
    }

    public function testkey(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveData($userQuery);
        $dataProvider->key('username');

        $this->assertSame(['admin', 'user', 'guest'], $dataProvider->getKeys());

        $dataProvider->getPagination()->pageSize(2);

        $this->assertSame(['admin', 'user'], $dataProvider->getKeys());

        $dataProvider->getPagination()->pageSize(2);
        $dataProvider->getPagination()->currentPage(2);

        $this->assertSame(['guest'], $dataProvider->getKeys());

        $dataProvider->getPagination()->pageSize(2);
        $dataProvider->getPagination()->currentPage(1);
        $dataProvider->key(static fn($arClass) => $arClass['email']);

        $this->assertSame(['admin@example.com', 'user@example.com'], $dataProvider->getKeys());

        $dataProvider->getPagination()->currentPage(3);

        $this->assertSame([], $dataProvider->getKeys());
    }

    public function testSort(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveData($userQuery);
        $dataProvider->sortParams(['sort' => '-id']);
        $user = $dataProvider->getARClasses();

        $this->assertCount(3, $user);
        $this->assertSame([3, 2, 1], $dataProvider->getKeys());
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $user[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $user[1]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $user[2]->getAttributes(),
        );

        $dataProvider->sortParams(['sort' => 'id']);
        $user = $dataProvider->getARClasses();

        $this->assertCount(3, $user);
        $this->assertSame([1, 2, 3], $dataProvider->getKeys());
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $user[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $user[1]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $user[2]->getAttributes(),
        );
    }
}
