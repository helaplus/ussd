<?php

use Illuminate\Support\Facades\Route;
use Helaplus\Ussd\Http\Controllers\EvotingController;
//
Route::post('/app', [EvotingController::class, 'app'])->name('ussd.app');