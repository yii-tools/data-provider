<?php

declare(strict_types=1);

return [
    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__),
            '@runtime' => '@root/tests/Support/runtime',
        ],
    ],

    'yiisoft/yii-db-migration' => [
        'updateNamespaces' => [
            'Forge\\Data\\Provider\\Tests\\Support\\Migration',
        ],
    ],
];
