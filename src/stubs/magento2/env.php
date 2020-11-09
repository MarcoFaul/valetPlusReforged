<?php
return [
    'backend' =>
        [
            'frontName' => 'admin',
        ],
    'db' =>
        [
            'connection' =>
                [
                    'indexer' =>
                        [
                            'host' => '127.0.0.1',
                            'dbname' => 'DBNAME',
                            'username' => 'root',
                            'password' => 'root',
                            'active' => '1',
                            'persistent' => null,
                            'model' => 'mysql4',
                            'engine' => 'innodb',
                            'initStatements' => 'SET NAMES utf8;',
                        ],
                    'default' =>
                        [
                            'host' => '127.0.0.1',
                            'dbname' => 'DBNAME',
                            'username' => 'root',
                            'password' => 'root',
                            'active' => '1',
                            'model' => 'mysql4',
                            'engine' => 'innodb',
                            'initStatements' => 'SET NAMES utf8;',
                        ],
                ],
            'table_prefix' => '',
        ],
    'crypt' =>
        [
            'key' => 'JkeEumwvvQBCDxypLPBozvrpF2rFNhNL',
        ],
    'session' =>
        [
            'save' => 'redis',
            'redis' =>
                [
                    'host' => '/tmp/redis.sock',
                    'port' => '6379',
                    'password' => '',
                    'timeout' => '2.5',
                    'persistent_identifier' => '',
                    'database' => '0',
                    'compression_threshold' => '2048',
                    'compression_library' => 'gzip',
                    'log_level' => '1',
                    'max_concurrency' => '6',
                    'break_after_frontend' => '5',
                    'break_after_adminhtml' => '30',
                    'first_lifetime' => '600',
                    'bot_first_lifetime' => '60',
                    'bot_lifetime' => '7200',
                    'disable_locking' => '0',
                    'min_lifetime' => '60',
                    'max_lifetime' => '2592000',
                ],
        ],
    'cache' =>
        [
            'frontend' =>
                [
                    'default' =>
                        [
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' =>
                                [
                                    'server' => '/tmp/redis.sock',
                                    'port' => '6379',
                                    'database' => '2',
                                ],
                        ],
                    'page_cache' =>
                        [
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' =>
                                [
                                    'server' => '/tmp/redis.sock',
                                    'port' => '6379',
                                    'database' => '1',
                                    'compress_data' => '0',
                                ],
                        ],
                ],
        ],
    'resource' =>
        [
            'default_setup' =>
                [
                    'connection' => 'default',
                ],
        ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'cache_types' =>
        [
            'config' => 1,
            'layout' => 1,
            'block_html' => 1,
            'collections' => 1,
            'reflection' => 1,
            'db_ddl' => 1,
            'eav' => 1,
            'full_page' => 1,
            'config_integration' => 1,
            'config_integration_api' => 1,
            'target_rule' => 1,
            'translate' => 1,
            'config_webservice' => 1,
            'compiled_config' => 0,
            'customer_notification' => 1,
        ],
    'install' =>
        [
            'date' => 'Wed, 19 Jul 2017 00:00:00 +0000',
        ],
    'queue' =>
        [
            'amqp' =>
                [
                    'host' => '',
                    'port' => '',
                    'user' => '',
                    'password' => '',
                    'virtualhost' => '/',
                    'ssl' => '',
                ],
        ],
];
