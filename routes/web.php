<?php

use Illuminate\Support\Facades\Route;
use Tanbhirhossain\LaravelLiveTerminal\Http\Controllers\TerminalController;

Route::prefix(config('terminal.path', 'live-terminal'))
    ->middleware(config('terminal.middleware', ['web', 'auth']))
    ->group(function () {
        Route::get('/', [TerminalController::class, 'index'])->name('terminal.index');
        Route::post('/run', [TerminalController::class, 'run'])->name('terminal.run');
    });