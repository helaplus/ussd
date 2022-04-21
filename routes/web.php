<?php

use Illuminate\Support\Facades\Route;
use Helaplus\Ussd\Http\Controllers\UssdController;

Route::post('/app', [UssdController::class, 'app'])->name('ussd.app');

Route::post('/app/{slug}', [UssdController::class, 'multiApp'])->name('ussd.multiApp');