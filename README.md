# Laravel Live Terminal

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tanbhirhossain/laravel-live-terminal.svg?style=flat-square)](https://packagist.org/packages/tanbhirhossain/laravel-live-terminal)
[![Total Downloads](https://img.shields.io/packagist/dt/tanbhirhossain/laravel-live-terminal.svg?style=flat-square)](https://packagist.org/packages/tanbhirhossain/laravel-live-terminal)

A simple and secure web-based terminal to run whitelisted Laravel Artisan commands directly from your browser.

## ⚠️ Security Warning

This package can execute commands on your server. It is **CRITICAL** that you protect it with proper authentication and authorization middleware. By default, it uses `['web', 'auth']`, but you should ensure this is appropriate for your application's user roles. **Do not expose this on a publicly accessible route without securing it.**

## Installation

You can install the package via composer:

```bash
composer require tanbhirhossain/laravel-live-terminal