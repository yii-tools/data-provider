<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Base;

use Yii\DataProvider\ActiveIteratorProvider;
use PHPUnit\Framework\TestCase;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Connection\ConnectionInterface;

abstract class AbstractActiveIteratorDataProviderTest extends TestCase
{
    protected ConnectionInterface $db;

    protected function setUp(): void
    {
        parent::setUp();

        $command = $this->db->createCommand();
        $schema = $this->db->getSchema();

        if ($schema->getTableSchema(User::tableName()) !== null) {
            $command->dropTable(User::tableName())->execute();
        }

        $this->db->createCommand()->createTable(
            User::tableName(),
            [
                'id' => $schema->createColumn('pk'),
                'username' => $schema->createColumn('string'),
                'email' => $schema->createColumn('string'),
            ],
        )->execute();

        $command->batchInsert(
            User::tableName(),
            ['username', 'email'],
            [
                ['admin', 'admin@example.com'],
                ['user', 'user@example.com'],
                ['guest', 'guest@example.com'],
            ],
        )->execute();
    }

    public function testCount(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(10);

        $this->assertSame(3, $activeIteratorProvider->count());
    }

    public function testGetIterator(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(10);

        $this->assertCount(3, $activeIteratorProvider->getIterator());
    }

    public function testGetLimit(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(10);

        $this->assertSame(10, $activeIteratorProvider->getLimit());
    }

    public function testGetOffset(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withOffset(10);

        $this->assertSame(10, $activeIteratorProvider->getOffset());
    }

    public function testImmutable(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = new ActiveIteratorProvider($userQuery);

        $this->assertNotSame($activeIteratorProvider, $activeIteratorProvider->withLimit(0));
        $this->assertNotSame($activeIteratorProvider, $activeIteratorProvider->withOffset(1));
    }

    public function testRead(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(5)->read();

        $this->assertCount(3, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $activeIteratorProvider[1]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $activeIteratorProvider[2]->getAttributes(),
        );
    }

    public function testReadOne(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(5)->readOne();

        $this->assertCount(1, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
    }

    public function testWithLimitWithNegative(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(-1)->withOffset(1)->read();

        $this->assertCount(3, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $activeIteratorProvider[1]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $activeIteratorProvider[2]->getAttributes(),
        );
    }

    public function testWithLimitWithZero(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(0)->withOffset(1)->read();

        $this->assertCount(0, $activeIteratorProvider);
    }

    public function testWithOffset(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(2)->withOffset(1)->read();

        $this->assertCount(2, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $activeIteratorProvider[1]->getAttributes(),
        );

        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(2)->withOffset(2)->read();

        $this->assertCount(1, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
    }

    public function testWithOffsetNegative(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(2)->withOffset(-1)->read();

        $this->assertCount(2, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $activeIteratorProvider[1]->getAttributes(),
        );
    }

    public function testWithOffsetZero(): void
    {
        $userQuery = new ActiveQuery(User::class, $this->db);
        $activeIteratorProvider = (new ActiveIteratorProvider($userQuery))->withLimit(2)->withOffset(0)->read();

        $this->assertCount(2, $activeIteratorProvider);
        $this->assertSame(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            $activeIteratorProvider[0]->getAttributes(),
        );
        $this->assertSame(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            $activeIteratorProvider[1]->getAttributes(),
        );
    }
}
