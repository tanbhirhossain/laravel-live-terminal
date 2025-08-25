# Laravel Live Terminal

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tanbhirhossain/laravel-live-terminal.svg?style=flat-square)](https://packagist.org/packages/tanbhirhossain/laravel-live-terminal)
[![Total Downloads](https://img.shields.io/packagist/dt/tanbhirhossain/laravel-live-terminal.svg?style=flat-square)](https://packagist.org/packages/tanbhirhossain/laravel-live-terminal)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A beautiful and secure web-based terminal to run whitelisted Laravel Artisan commands directly from your browser.


---

## ⚠️ Critical Security Warning

This package allows you to execute shell commands on your server from a web interface. If not properly secured, it can expose your entire application and server to catastrophic vulnerabilities.

-   **NEVER** deploy this to a production environment with weak or no authentication.
-   **ALWAYS** protect the terminal route with middleware that strictly limits access to trusted administrators.
-   **BE CAREFUL** with the commands you add to the `allowed_commands` whitelist. Do not allow commands that can write arbitrary files or execute arbitrary code.

**You are responsible for securing this tool within your application.**

---

## Installation

You can install the package via Composer:

```bash
composer require tanbhirhossain/laravel-live-terminal
Next, you must publish the configuration file. This is a required step to manage your command whitelist and security settings.
code
Bash
php artisan vendor:publish --provider="Tanbhirhossain\LaravelLiveTerminal\TerminalServiceProvider" --tag="terminal-config"
This will create a config/terminal.php file in your project.
Usage
Make sure you have a working authentication system (e.g., Laravel Breeze, Jetstream).
By default, the terminal is available at http://your-app.test/live-terminal.
You must be logged in to access this route, as defined by the default auth middleware in the config.
Once you access the URL, you will see the terminal interface and can begin running commands that you have whitelisted in the configuration file.
Configuration
Customization is done in the config/terminal.php file. Let's break down each option.
PHP Executable Path
The package needs to know where your server's PHP executable is located. It tries to find it automatically, but on some server configurations (especially on Windows with WAMP/XAMPP), this can fail.
If your commands are not working, you can set the path manually.
code
PHP
// config/terminal.php

'php_path' => null,

// Example for Windows:
// 'php_path' => 'C:\php\php.exe',

// Example for Linux (usually found automatically):
// 'php_path' => '/usr/bin/php',
Allowed Commands (Whitelist)
This is your primary security control. It is an array of base commands that are allowed to run. The terminal will reject any command not on this list.
code
PHP
// config/terminal.php

'allowed_commands' => [
    'list',
    'help',
    '--version',
    'route:list',
    'view:clear',
    'config:clear',
    'cache:clear',
    'migrate',
    'migrate:status',
    'schedule:list',

    // Add your own safe commands here. For example:
    'queue:failed',
    'db:seed',
],
Note: The check is performed on the base command. This means if route:list is allowed, a user can also run route:list --json --path=api. Be mindful of the commands and options you enable.
Route Middleware
This is your primary security gate. It's an array of middleware that will be applied to the terminal's routes.
It is strongly recommended to add your own middleware to restrict access to only administrators.
code
PHP
// config/terminal.php

'middleware' => ['web', 'auth'],

// Recommended example for a project with an 'isAdmin' middleware:
// 'middleware' => ['web', 'auth', 'isAdmin'],

// Example for a project using roles/permissions (e.g., Spatie's package):
// 'middleware' => ['web', 'auth', 'role:super-admin'],
Route Path
This setting controls the URL where the terminal is accessible.
code
PHP
// config/terminal.php

'path' => 'live-terminal',

// You can change this to anything you want, for example:
// 'path' => 'super-secret-admin-console',
After changing the path, remember to clear your route cache: php artisan route:clear.
Troubleshooting
404 Not Found: Your route cache is likely out of date. Run php artisan route:clear.
White Screen or JavaScript Errors: Your application cache is likely out of date, preventing the service provider from loading. Run php artisan optimize:clear.
Commands return Apache/Nginx help menu: The package cannot find your PHP executable. Set the correct path in config/terminal.php under the php_path key.