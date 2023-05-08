<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Base;

use PHPUnit\Framework\TestCase;
use Yii\DataProvider\IteratorDataProviderInterface;
use Yii\DataProvider\Tests\Support\ActiveRecord\User;
use Yiisoft\Db\Connection\ConnectionInterface;

abstract class AbstractIteratorDataProviderTest extends TestCase
{
    protected ConnectionInterface $db;
    protected IteratorDataProviderInterface $iteratorProvider;

    public function testCount(): void
    {
        $this->assertSame(3, $this->iteratorProvider->count());
    }

    public function testGetIterator(): void
    {
        $this->assertCount(3, $this->iteratorProvider->getIterator());
    }

    public function testGetLimit(): void
    {
        $this->assertSame(10, $this->iteratorProvider->getLimit());
    }

    public function testGetOffset(): void
    {
        $this->assertSame(0, $this->iteratorProvider->getOffset());
    }

    public function testGetTotalPage(): void
    {
        $this->assertSame(1, $this->iteratorProvider->getTotalPages());
    }

    public function testImmutable(): void
    {
        $this->assertNotSame($this->iteratorProvider, $this->iteratorProvider->withLimit(0));
        $this->assertNotSame($this->iteratorProvider, $this->iteratorProvider->withOffset(0));
    }

    public function testRead(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(5)->read();

        $this->assertCount(3, $iteratorProvider);
        $this->assertEquals(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
        $this->assertEquals(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            is_object($iteratorProvider[1]) ? $iteratorProvider[1]->getAttributes() : $iteratorProvider[1],
        );
        $this->assertEquals(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            is_object($iteratorProvider[2]) ? $iteratorProvider[2]->getAttributes() : $iteratorProvider[2],
        );
    }

    public function testReadOne(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(5)->readOne();

        $this->assertCount(1, $iteratorProvider);
        $this->assertEquals(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
    }

    public function testWithLimitWithNegative(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(-1)->withOffset(1)->read();

        $this->assertCount(3, $iteratorProvider);
        $this->assertEquals(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
        $this->assertEquals(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            is_object($iteratorProvider[1]) ? $iteratorProvider[1]->getAttributes() : $iteratorProvider[1],
        );
        $this->assertEquals(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            is_object($iteratorProvider[2]) ? $iteratorProvider[2]->getAttributes() : $iteratorProvider[2],
        );
    }

    public function testWithLimitWithZero(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(0)->withOffset(1)->read();

        $this->assertCount(0, $iteratorProvider);
    }

    public function testWithOffset(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(2)->withOffset(1)->read();

        $this->assertCount(2, $iteratorProvider);
        $this->assertEquals(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
        $this->assertEquals(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            is_object($iteratorProvider[1]) ? $iteratorProvider[1]->getAttributes() : $iteratorProvider[1],
        );

        $iteratorProvider = $this->iteratorProvider->withLimit(2)->withOffset(2)->read();

        $this->assertCount(1, $iteratorProvider);
        $this->assertEquals(
            ['id' => 3, 'username' => 'guest', 'email' => 'guest@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
    }

    public function testWithOffsetNegative(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(2)->withOffset(-1)->read();

        $this->assertCount(2, $iteratorProvider);
        $this->assertEquals(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
        $this->assertEquals(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            is_object($iteratorProvider[1]) ? $iteratorProvider[1]->getAttributes() : $iteratorProvider[1],
        );
    }

    public function testWithOffsetZero(): void
    {
        $iteratorProvider = $this->iteratorProvider->withLimit(2)->withOffset(0)->read();

        $this->assertCount(2, $iteratorProvider);
        $this->assertEquals(
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com'],
            is_object($iteratorProvider[0]) ? $iteratorProvider[0]->getAttributes() : $iteratorProvider[0],
        );
        $this->assertEquals(
            ['id' => 2, 'username' => 'user', 'email' => 'user@example.com'],
            is_object($iteratorProvider[1]) ? $iteratorProvider[1]->getAttributes() : $iteratorProvider[1],
        );
    }

    protected function createSchema(): void
    {
        $command = $this->db->createCommand();
        $schema = $this->db->getSchema();

        if ($schema->getTableSchema(User::tableName()) !== null) {
            $command->dropTable(User::tableName())->execute();
        }

        $this->db
            ->createCommand()
            ->createTable(
                User::tableName(),
                [
                    'id' => $schema->createColumn('pk'),
                    'username' => $schema->createColumn('string'),
                    'email' => $schema->createColumn('string'),
                ],
            )
            ->execute();

        $command
            ->batchInsert(
                User::tableName(),
                ['username', 'email'],
                [
                    ['admin', 'admin@example.com'],
                    ['user', 'user@example.com'],
                    ['guest', 'guest@example.com'],
                ],
            )
            ->execute();
    }

    protected function dropSchema(): void
    {
        $this->db->createCommand()->dropTable(User::tableName())->execute();
    }
}
