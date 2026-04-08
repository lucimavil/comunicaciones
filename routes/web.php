<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComunicacionController;
use App\Http\Controllers\CampaniaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth'])->group(function(){

Route::resource('comunicaciones', ComunicacionController::class);

});
 Route::get(
'/comunicaciones/{id}/dashboard',
[ComunicacionController::class,'dashboard']
)->name('comunicaciones.dashboard');

Route::get('/campanias',[CampaniaController::class,'index'])->name('campanias.index');

Route::get('/campanias/create',[CampaniaController::class,'create'])->name('campanias.create');

Route::post('/campanias', [CampaniaController::class,'store'])->name('campanias.store');

require __DIR__.'/auth.php';
