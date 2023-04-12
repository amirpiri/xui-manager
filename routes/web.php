<?php

use App\Http\Controllers\Client\ClientListController;
use App\Http\Controllers\Client\InboundController;
use App\Http\Controllers\Client\NewClientController;
use App\Http\Controllers\Client\RenewClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(SubscriptionController::class)->group(function () {
    Route::get('generate/subs/{uuid}', 'generateSubscriptionLink')->name('generate-subscription-link');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'clients', 'as' => 'client.'], function () {
        Route::get('/', ClientListController::class)->name('list');

        Route::get('{clientId}/renew', [RenewClientController::class, 'show'])->name('renew');
        Route::put('{clientId}/renew', [RenewClientController::class, 'update'])->name('renew-client.update');

        Route::get('create', [NewClientController::class, 'create'])->name('create');
        Route::post('store', [NewClientController::class, 'store'])->name('store');
    });
    Route::get('/inbounds', InboundController::class)->name('inbounds');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
