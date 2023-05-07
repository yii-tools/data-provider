<p align="center">
    <a href="https://github.com/yii-tools/awesome-component" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/121752654?s=200&v=4" height="100px">
    </a>
    <h1 align="center">Data Provider for YiiFramework v. 3.0.</h1>
    <br>
</p>

## Requirements

The minimun version of PHP required by this package is PHP 8.1.

For install this package, you need [composer](https://getcomposer.org/).

## Install

```shell
composer require yii-tools/data-provider
```

## Usage

### ActiveIteratorDataProvider

```php
<?php

declare(strict_types=1);

use Yii\DataProvider\ActiveIteratorDataProvider;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Connection\ConnectionInterface;

/** @var ConnectionInterface $db */
$userQuery = new ActiveQuery(User::class, $db);
$activeIteratorDataProvider = new ActiveDataProvider($userQuery);
```

### ArrayIteratorDataProvider

```php
<?php

declare(strict_types=1);

use Yii\DataProvider\ArrayIteratorDataProvider;

$arrayIteratorDataProvider = new ArrayIteratorDataProvider(
    [
        ['id' => 1, 'name' => 'name1'],
        ['id' => 2, 'name' => 'name2'],
        ['id' => 3, 'name' => 'name3'],
    ],
);
```

### QueryIteratorDataProvider

```php
<?php

declare(strict_types=1);

use Yii\DataProvider\QueryIteratorDataProvider;

/** @var ConnectionInterface $db */
$queryIteratorDataProvider = new QueryIteratorDataProvider((new Query($db))->select('*')->from('{{%user}}'));
```

### SQLIteratorDataProvider

```php
<?php

declare(strict_types=1);

use Yii\DataProvider\SQLIteratorDataProvider;
use Yiisoft\Db\Connection\ConnectionInterface;

/** @var ConnectionInterface $db */
$sqlIteratorDataProvider = new SQLIteratorDataProvider($db, 'SELECT * FROM {{%user}}');
```

## Testing

[Check the documentation testing](/docs/testing.md) to learn about testing.

## CI status

[![Build Status](https://github.com/yii-tools/data-provider/workflows/build/badge.svg)](https://github.com/yii-tools/data-provider/actions?query=workflow%3Abuild)
[![codecov](https://codecov.io/gh/yii-tools/data-provider/branch/main/graph/badge.svg?token=KB6T5KMGED)](https://codecov.io/gh/yii-tools/data-provider)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii-tools%2Fdata-provider%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/yii-tools/data-provider/main)
[![static analysis](https://github.com/yii-tools/data-provider/workflows/static%20analysis/badge.svg)](https://github.com/yii-tools/data-provider/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yii-tools/data-provider/coverage.svg)](https://shepherd.dev/github/yii-tools/data-provider)
[![StyleCI](https://github.styleci.io/repos/518593668/shield?branch=main)](https://github.styleci.io/repos/518593668?branch=main)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Our social networks

[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/Terabytesoftw)
