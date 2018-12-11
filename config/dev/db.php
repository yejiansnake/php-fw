<?php

return [
    'db_admin' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=admin',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8mb4',
    ],
    'db_client' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=client',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8mb4',
    ],
];