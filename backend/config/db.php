<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf('pgsql:host=%s;port=5432;dbname=%s', getenv('POSTGRES_HOST') ?: 'postgres', getenv('POSTGRES_DB') ?: 'glavprirod'),
    'username' => getenv('POSTGRES_USER') ?: 'gpuser',
    'password' => getenv('POSTGRES_PASSWORD') ?: 'gppass',
    'charset' => 'utf8',
];
