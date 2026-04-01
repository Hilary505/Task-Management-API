<?php

return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '3306',
    'dbname'   => getenv('DB_NAME') ?: 'taskmanager',
    'username' => getenv('DB_USER') ?: 'taskuser',
    'password' => getenv('DB_PASS') ?: '1234',
];
