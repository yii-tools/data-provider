<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Support\ActiveRecord;

use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * User Active Record.
 *
 * Database fields:
 *
 * @property int $id
 * @property string $username
 * @property string $email
 */
final class User extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }
}
