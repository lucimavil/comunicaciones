<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Campania;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/home';
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

        $hoy = Carbon::today();

        $estadoGeneral = [
            'campanias_programadas_hoy' => Campania::where('estado', 'programada')
                ->whereDate('fecha_programada', $hoy)
                ->count(),

            'campanias_en_revision' => Campania::where('estado', 'revision')
                ->count(),
        ];

        $view->with('estadoGeneral', $estadoGeneral);
    });
    }
}
