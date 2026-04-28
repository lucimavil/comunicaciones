<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComunicacionController;
use App\Http\Controllers\CampaniaController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

  // HOME PRINCIPAL
    Route::get('/home', function () {
        return view('home');
    })->name('home');
    // MODULO COMUNICACIONES
    Route::prefix('comunicaciones')->group(function () {

        Route::get('/', function () {
            return view('comunicaciones.index');
        })->name('comunicaciones.index');

        Route::resource('campanias', CampaniaController::class);
    });
    // MODULO CAMPAÑA
    Route::resource('campanias', CampaniaController::class);
    Route::get('/campanias/{id}', [CampaniaController::class, 'show'])->name('campanias.show');

  
    Route::patch('/campanias/{campania}/programar', [CampaniaController::class, 'programar'])
    ->name('campanias.programar');
    
    Route::get('/campanias', [CampaniaController::class, 'index'])->name('campanias.index');
    Route::get('/campanias/create', [CampaniaController::class, 'create'])->name('campanias.create');
    Route::post('/campanias', [CampaniaController::class, 'store'])->name('campanias.store');

    Route::post('/campanias/probar-segmentacion', [CampaniaController::class, 'probarSegmentacion'])
        ->name('campanias.probar-segmentacion');
    
        Route::post('/campanias/guardar-borrador', [CampaniaController::class, 'guardarBorrador'])
    ->name('campanias.guardar-borrador');

    Route::get('/campanias/{id}/edit', [CampaniaController::class, 'edit'])->name('campanias.edit');
    Route::patch('/campanias/{campania}/cancelar', [CampaniaController::class, 'cancelar'])
    ->name('campanias.cancelar');

    // MODULO ENCUESTAS
    //MODULO MENSAJERIA
    //MODULO  INTERCONSULTAS
});



require __DIR__.'/auth.php';