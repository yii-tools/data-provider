<p align="center">
    <a href="https://github.com/php-forge/data-provider" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/103309199?s=400&u=ca3561c692f53ed7eb290d3bb226a2828741606f&v=4" height="100px">
    </a>
    <h1 align="center">Proveedor de datos para yiisoft/db.</h1>
    <br>
</p>

[![Build Status](https://github.com/php-forge/data-provider/workflows/build/badge.svg)](https://github.com/php-forge/data-provider/actions?query=workflow%3Abuild)
[![codecov](https://codecov.io/gh/php-forge/data-provider/branch/main/graph/badge.svg?token=KB6T5KMGED)](https://codecov.io/gh/php-forge/data-provider)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fphp-forge%2Ftemplate%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/php-forge/data-provider/main)
[![static analysis](https://github.com/php-forge/data-provider/workflows/static%20analysis/badge.svg)](https://github.com/php-forge/data-provider/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/php-forge/data-provider/coverage.svg)](https://shepherd.dev/github/php-forge/data-provider)

## Instalación

```shell
composer require forge/data-provider
```

## Análisis estático

El código se analiza estáticamente con [Psalm](https://psalm.dev/docs). Para ejecutarlo:

```shell
./vendor/bin/psalm
```

## Como usar el proveedor de datos

### ArrayDataProvider

```php
<?php

declare(strict_types=1);

use Forge\Data\Provider\ArrayDataProvider;
use Yiisoft\ActiveRecord\ActiveQuery;

private array $simpleArray = [
    ['name' => 'zero'],
    ['name' => 'one'],
    ['name' => 'two'],
];

$dataProvider = new ArrayDataProvider();
$dataProvider = $dataProvider->allData($this->simpleArray);
```

### ActiveDataProvider

```php
<?php

declare(strict_types=1);

use Forge\Data\Provider\ActiveDataProvider;
use Yiisoft\ActiveRecord\ActiveQuery;

$userQuery = new ActiveQuery(User::class, $this->db);
$dataProvider = new ActiveDataProvider($userQuery);
```


## Pruebas de mutación

Las pruebas de mutación se comprueban con [Infection](https://infection.github.io/). Para ejecutarlo:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

## Pruebas unitarias

Las pruebas unitarias se comprueban con [PHPUnit](https://phpunit.de/). Para ejecutarlo:

```shell
./vendor/bin/phpunit
```

## Calidad y estilo de código

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/54109a976d414636883dff0993fc96b6)](https://www.codacy.com/gh/php-forge/data-provider/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=php-forge/data-provider&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://github.styleci.io/repos/518593668/shield?branch=main)](https://github.styleci.io/repos/518593668/shield?branch=main)

## Licencia

El paquete `php-forge/data-provider` es software libre. Se publica bajo los términos de la Licencia BSD.
Consulte [`LICENSE`](./LICENSE.md) para obtener más información.

Mantenido por [Terabytesoftw](https://github.com/terabytesoftw).

## Nuestras redes sociales

[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/PhpForge)
