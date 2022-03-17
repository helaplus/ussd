<?php
namespace Helaplus\Ussd\Routes;

use Illuminate\Support\Facades\Route;
use Helaplus\Ussd\Http\Controllers\UssdController;
//
Route::get('/app', [UssdController::class, 'index'])->name('ussd.index');