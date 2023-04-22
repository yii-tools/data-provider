<?php

declare(strict_types=1);

namespace Yii\DataProvider\Tests\Support\Helper;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;

final class SqliteConnection
{
    public static function getConnection(): ConnectionInterface
    {
        $dsn = new Dsn('sqlite', 'memory');
        $pdoDriver = new Driver($dsn->asString());
        $schemaCache = new SchemaCache(new ArrayCache());

        return new Connection($pdoDriver, $schemaCache);
    }
}
