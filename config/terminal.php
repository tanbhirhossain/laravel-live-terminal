<?php


return [
     // The URI path for the terminal
    'php_path' => null,

    
    'allowed_commands' => [
        'list', 'help', '--version', 'route:list', 'view:clear',
        'config:clear', 'cache:clear', 'migrate', 'migrate:status',
        'schedule:list',
    ],
    // Add middleware for the terminal routes
    'middleware' => ['web', 'auth'], // IMPORTANT: Default to secure middleware
   
];