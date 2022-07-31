<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests\Support\ActiveRecord;

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

    public function getId(): string
    {
        return (string) $this->getAttribute('id');
    }

    public function getEmail(): string
    {
        return (string) $this->getAttribute('email');
    }

    public function getUsername(): string
    {
        return (string) $this->getAttribute('username');
    }

    public function email(string $value): void
    {
        $this->setAttribute('email', $value);
    }

    public function username(string $value): void
    {
        $this->setAttribute('username', $value);
    }
}
