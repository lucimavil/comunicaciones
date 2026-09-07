<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComunicacionController;
use App\Http\Controllers\CampaniaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InterconsultaController;


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
 
    Route::delete('/campanias/{campania}', [CampaniaController::class, 'destroy'])
    ->name('campanias.destroy');
    
    Route::get('/campanias/{campania}/dashboard', [CampaniaController::class, 'dashboard'])
        ->name('campanias.dashboard');
   

    Route::get('/campanias/{campania}/dashboard/excel', [CampaniaController::class, 'exportarDashboardExcel'])
        ->name('campanias.dashboard.excel');

    // MODULO COMUNICACIONES
     Route::resource('comunicacion', ComunicacionController::class);
    Route::get('/comunicacion/{id}', [ComunicacionController::class, 'show'])->name('comunicacion.show');

  
    Route::patch('/comunicacion/{comunicacion}/programar', [ComunicacionController::class, 'programar'])
    ->name('comunicacion.programar');
    
    Route::get('/comunicacion', [ComunicacionController::class, 'index'])->name('comunicacion.index');
    Route::get('/comunicacion/create', [ComunicacionController::class, 'create'])->name('comunicacion.create');
    Route::post('/comunicacion', [ComunicacionController::class, 'store'])->name('comunicacion.store');

    Route::post('/comunicacion/probar-segmentacion', [ComunicacionController::class, 'probarSegmentacion'])
        ->name('comunicacion.probar-segmentacion');
    
        Route::post('/comunicacion/guardar-borrador', [ComunicacionController::class, 'guardarBorrador'])
    ->name('comunicacion.guardar-borrador');

    Route::get('/comunicacion/{id}/edit', [ComunicacionController::class, 'edit'])->name('comunicacion.edit');
 
    Route::delete('/comunicacion/{comunicacion}', [ComunicacionController::class, 'destroy'])
    ->name('comunicacion.destroy');
    
    Route::get('/comunicacion/{comunicacion}/dashboard', [ComunicacionController::class, 'dashboard'])
        ->name('comunicacion.dashboard');
   

    Route::get('/comunicacion/{comunicacion}/dashboard/excel', [ComunicacionController::class, 'exportarDashboardExcel'])
        ->name('comunicacion.dashboard.excel');

        /*
|--------------------------------------------------------------------------
| Mensajería WhatsApp
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::view('/mensajeria', 'mensajeria.index')
        ->name('mensajeria.index');

    Route::view('/mensajeria/calendario', 'mensajeria.calendario')
        ->name('mensajeria.calendario');


    Route::get('/mensajeria/api/mensajes', function (\Illuminate\Http\Request $request) {

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->get(
                'https://hrc-mensajeria.sanluis.gob.ar:8081/mensajeria-data/mensajes',
                [
                    'start' => $request->start,
                    'end' => $request->end,
                ]
            );

        return response()->json(
            $response->json(),
            $response->status()
        );

    })->name('mensajeria.api.mensajes');


    Route::get('/mensajeria/api/envios', function (\Illuminate\Http\Request $request) {

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->get(
                'https://hrc-mensajeria.sanluis.gob.ar:8081/mensajeria-data/envios',
                [
                    'start' => $request->start,
                    'end' => $request->end,
                ]
            );

        return response()->json(
            $response->json(),
            $response->status()
        );

    })->name('mensajeria.api.envios');
    Route::get('/mensajeria/api/detalle-mensajes', function (\Illuminate\Http\Request $request) {

    $response = Http::withoutVerifying()
        ->timeout(60)
        ->get(
            'https://hrc-mensajeria.sanluis.gob.ar:8081/mensajeria-data/detalle-mensajes',
            [
                'id_envio' => $request->id_envio,
            ]
        );

    return response()->json(
        $response->json(),
        $response->status()
    );

})->name('mensajeria.api.detalle');

});
Route::get(
        '/interconsultas',
        [
            InterconsultaController::class,
            'index'
        ]
    )->name('interconsultas.index');


    Route::get(
        '/interconsultas/{id}',
        [
            InterconsultaController::class,
            'show'
        ]
    )->name('interconsultas.show');

 });
    

  

require __DIR__.'/auth.php';