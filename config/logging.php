<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'channels' => [
        'stack' => ['driver' => 'stack', 'channels' => ['single'], 'ignore_exceptions' => false],
        'single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => 'debug'],
        'daily' => ['driver' => 'daily', 'path' => storage_path('logs/laravel.log'), 'level' => 'debug', 'days' => 14],
        'stderr' => ['driver' => 'monolog', 'handler' => StreamHandler::class, 'formatter' => env('LOG_STDERR_FORMATTER'), 'with' => ['stream' => 'php://stderr']],
        'syslog' => ['driver' => 'syslog', 'level' => 'debug'],
        'errorlog' => ['driver' => 'errorlog', 'level' => 'debug'],
        'null' => ['driver' => 'monolog', 'handler' => NullHandler::class],
        'emergency' => ['path' => storage_path('logs/laravel.log')],
    ],
];
