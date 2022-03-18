<?php

use Illuminate\Support\Facades\Route;
use Helaplus\Ussd\Http\Controllers\UssdController;
//
Route::post('/app', [UssdController::class, 'app'])->name('ussd.app');