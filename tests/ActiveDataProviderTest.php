<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests;

use Forge\Data\Provider\ActiveDataProvider;
use Forge\Data\Provider\Tests\Support\ActiveRecord\User;
use Forge\Data\Provider\Tests\Support\Helper\SqliteConnection;
use PHPUnit\Framework\TestCase;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Data\Reader\Sort;

final class ActiveDataProviderTest extends TestCase
{
    public function testCount(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $dataProvider = $dataProvider->withLimit(10);

        $this->assertSame(3, $dataProvider->count());
    }

    public function testGetKeys(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $dataProvider = $dataProvider->withLimit(3);

        $this->assertSame([1, 2, 3], $dataProvider->getKeys());

        $dataProvider = $dataProvider->withLimit(2);

        $this->assertSame([1, 2], $dataProvider->getKeys());

        $dataProvider = $dataProvider->withOffset(2);

        $this->assertSame([3], $dataProvider->getKeys());
    }

    public function testGetSort(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $this->assertNull($dataProvider->getSort());
    }

    public function testkey(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $dataProvider = $dataProvider->key('username')->withLimit(3);

        $this->assertSame(['admin', 'user', 'guest'], $dataProvider->getKeys());

        $dataProvider = $dataProvider->withLimit(2);

        $this->assertSame(['admin', 'user'], $dataProvider->getKeys());

        $dataProvider = $dataProvider->withOffset(2);

        $this->assertSame(['guest'], $dataProvider->getKeys());

        $dataProvider = $dataProvider->withLimit(2)->withOffset(0)->key(static fn ($arClass) => $arClass['email']);

        $this->assertSame(['admin@example.com', 'user@example.com'], $dataProvider->getKeys());

        $dataProvider = $dataProvider->withOffset(4);

        $this->assertSame([], $dataProvider->getKeys());
    }

    public function testRead(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $user = $dataProvider->withLimit(5)->read();

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

    public function testReadOne(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $user = $dataProvider->withLimit(5)->readOne();

        $this->assertCount(1, $user);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $user[0]->getAttributes(),
        );
    }

    public function testSort(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $dataProvider = $dataProvider->withLimit(5)->sortParams('-id');
        $user = $dataProvider->read();

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

        $dataProvider = $dataProvider->sortParams('id');
        $user = $dataProvider->read();

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

    public function testWithSort(): void
    {
        $userQuery = new ActiveQuery(User::class, (new SqliteConnection())->createConnection());
        $dataProvider = new ActiveDataProvider($userQuery);
        $dataProvider = $dataProvider
            ->key('email')
            ->withLimit(3)
            ->withSort(
                Sort::only(
                    [
                        'email' => [
                            'asc' => ['email' => SORT_ASC],
                            'desc' => ['email' => SORT_DESC],
                            'default' => 'desc',
                        ],
                    ],
                )
            );
        $user = $dataProvider->read();

        $this->assertCount(3, $user);
        $this->assertSame(['user@example.com', 'guest@example.com', 'admin@example.com'], $dataProvider->getKeys());

        $dataProvider = $dataProvider
            ->key('email')
            ->withLimit(3)
            ->withSort(
                Sort::only(
                    [
                        'email' => [
                            'asc' => ['email' => SORT_ASC],
                            'desc' => ['email' => SORT_DESC],
                            'default' => 'asc',
                        ],
                    ],
                )
            );
        $user = $dataProvider->read();

        $this->assertCount(3, $user);
        $this->assertSame(['admin@example.com', 'guest@example.com', 'user@example.com'], $dataProvider->getKeys());
    }
}
