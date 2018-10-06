<?php

declare(strict_types=1);

return  [
    'cache' => [
        'adapter' => 'chain',
        'adapters' => [
            'array' => [
                'default_lifetime' => 0,
                'store_serialized' => true,
            ],
            'apcu' => [
                'namespace' => 'my_namespace',
                'default_lifetime' => 60,
                'version' => null,
            ],
            'filesystem' => [
                'namespace' => 'my_namespace',
                'default_lifetime' => 0,
                'directory' => '/path/to/cache/folder',
            ],
            'redis' => [
                'instance' => 'my_redis_client_alias', // the redis client will be looked up in the container
                'namespace' => 'my_redis_namespace',
                'default_lifetime' => 3600,
            ],
            'pdo' => [
                // 'instance' => 'my_PDO_or_DoctrineDbalConnection_alias',
                'dns' => 'mysql:dbname=testdb;host=127.0.0.1',
                'default_lifetime' => 7200,
                'options' => [
                    'db_username' => 'user',
                    'db_password' => 'secret',
                    'db_table' => 'cache_items',
                    'db_id_col' => 'id',
                    'db_lifetime_col' => 'lifetime',
                    'db_data_col' => 'cache',
                    'db_time_col' => 'time',
                    'db_connection_options' => [],
                ],
            ],
            'php_files' => [
                'namespace' => 'my_namespace',
                'default_lifetime' => 3600,
                'directory' => '/path/to/cache/folder',
            ],
            'chain' => [
                'adapters' => [
                    'apcu', 'redis',
                ],
            ],
        ],
    ],
];
