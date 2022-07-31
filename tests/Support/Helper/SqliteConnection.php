<?php

declare(strict_types=1);

namespace Forge\Data\Provider\Tests\Support\Helper;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Cache\QueryCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\ConnectionPDO;
use Yiisoft\Db\Sqlite\PDODriver;

final class SqliteConnection
{
    private string $drivername = 'sqlite';
    private string $dsn = 'sqlite:' . __DIR__ . '/../yiitest.sq3';
    private string $charset = 'UTF8MB4';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new PDODriver($this->dsn, '', '');
        $pdoDriver->setCharset($this->charset);

        return new ConnectionPDO($pdoDriver, $this->createQueryCache(), $this->createSchemaCache());
    }

    private function createCache(): CacheInterface
    {
        return new Cache(new ArrayCache());
    }

    private function createQueryCache(): QueryCache
    {
        return new QueryCache($this->createCache());
    }

    private function createSchemaCache(): SchemaCache
    {
        return new SchemaCache($this->createCache());
    }
}
