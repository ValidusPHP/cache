<?php

declare(strict_types=1);

return  [
    'cache' => [
        'adapter' => 'chain',
        'adapters' => [
            'array' => [
                'namespace' => 'my_namespace',
                'default_lifetime' => 0,
            ],
            'redis' => [
                'instance' => 'my_redis_client_alias', // the redis client will be looked up in the container
                'namespace' => 'my_redis_namespace',
                'default_lifetime' => 3600,
            ],
            'chain' => [
                'adapters' => ['array', 'null'],
            ],
        ],
    ],
];
