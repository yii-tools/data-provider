<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Base;

use PHPUnit\Framework\TestCase;
use Yii\DataProvider\SqlIteratorDataProvider;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yiisoft\Db\Connection\ConnectionInterface;

abstract class AbstractSqlIteratorDataProviderTest extends TestCase
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
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))->withLimit(10);

        $this->assertSame(3, $sqlIteratorDataProvider->count());
    }

    public function testGetIterator(): void
    {
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))->withLimit(10);

        $this->assertCount(3, $sqlIteratorDataProvider->getIterator());
    }

    public function testImmutable(): void
    {
        $sqlIteratorDataProvider = new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}');

        $this->assertNotSame($sqlIteratorDataProvider, $sqlIteratorDataProvider->withLimit(10));
        $this->assertNotSame($sqlIteratorDataProvider, $sqlIteratorDataProvider->withOffset(10));
    }

    public function testRead(): void
    {
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))->withLimit(5);
        $data = $sqlIteratorDataProvider->read();

        $this->assertCount(3, $data);
        $this->assertSame(['id' => '1', 'username' => 'admin', 'email' => 'admin@example.com'], $data[0]);
        $this->assertSame(['id' => '2', 'username' => 'user', 'email' => 'user@example.com'], $data[1]);
        $this->assertSame(['id' => '3', 'username' => 'guest', 'email' => 'guest@example.com'], $data[2]);
    }

    public function testReadOne(): void
    {
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))->withLimit(1);
        $data = $sqlIteratorDataProvider->read();

        $this->assertCount(1, $data);
        $this->assertSame(['id' => '1', 'username' => 'admin', 'email' => 'admin@example.com'], $data[0]);
    }

    public function testWithOffset(): void
    {
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))
            ->withLimit(2)
            ->withOffset(1);
        $data = $sqlIteratorDataProvider->read();

        $this->assertCount(2, $data);
        $this->assertSame(['id' => '1', 'username' => 'admin', 'email' => 'admin@example.com'], $data[0]);
        $this->assertSame(['id' => '2', 'username' => 'user', 'email' => 'user@example.com'], $data[1]);

        $sqlIteratorDataProvider = $sqlIteratorDataProvider->withLimit(2)->withOffset(2);
        $data = $sqlIteratorDataProvider->read();

        $this->assertCount(1, $data);
        $this->assertSame(['id' => '3', 'username' => 'guest', 'email' => 'guest@example.com'], $data[0]);
    }

    public function testWithOffsetNegative(): void
    {
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))
            ->withLimit(2)
            ->withOffset(-1);
        $data = $sqlIteratorDataProvider->read();

        $this->assertCount(2, $data);
        $this->assertSame(['id' => '1', 'username' => 'admin', 'email' => 'admin@example.com'], $data[0]);
        $this->assertSame(['id' => '2', 'username' => 'user', 'email' => 'user@example.com'], $data[1]);
    }

    public function testWithOffsetZero(): void
    {
        $sqlIteratorDataProvider = (new SqlIteratorDataProvider($this->db, 'SELECT * FROM {{%user}}'))
            ->withLimit(2)
            ->withOffset(0);
        $data = $sqlIteratorDataProvider->read();

        $this->assertCount(2, $data);
        $this->assertSame(['id' => '1', 'username' => 'admin', 'email' => 'admin@example.com'], $data[0]);
        $this->assertSame(['id' => '2', 'username' => 'user', 'email' => 'user@example.com'], $data[1]);
    }
}
