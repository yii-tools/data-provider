<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests\Support\Migration;

use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

/**
 * Class M202207301650User
 */
final class M202207301650User implements RevertibleMigrationInterface
{
    /**
     * @throws InvalidConfigException|NotSupportedException
     */
    public function up(MigrationBuilder $b): void
    {
        $tableOptions = null;

        if ($b->getDb()->getName() === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 ENGINE=InnoDB';
        }

        $b->createTable(
            '{{%user}}',
            [
                'id' => $b->primaryKey()->notNull()->unsigned(),
                'username' => $b->string(255)->defaultValue('')->notNull(),
                'email' => $b->string(255)->defaultValue('')->notNull(),
            ],
            $tableOptions
        );

        $b->batchInsert(
            '{{%user}}',
            [
                'username',
                'email',
            ],
            [
                [
                    'admin',
                    'admin@example.com'
                ],
                [
                    'user',
                    'user@example.com',
                ],
                [
                    'guest',
                    'guest@example.com',
                ],
            ]
        );
    }

    /**
     * @throws InvalidConfigException|NotSupportedException
     */
    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%user}}');
    }
}
