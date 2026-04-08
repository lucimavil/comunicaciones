<?php

namespace App\Http\Controllers;

use App\Models\Comunicacion;
use App\Models\User;
use App\Models\Segmento;
use Illuminate\Http\Request;

class ComunicacionController extends Controller
{
    public function index()
    {
        $comunicaciones = Comunicacion::with('responsable')->latest()->get();

        return view('comunicaciones.index',compact('comunicaciones'));
    }

    public function create()
    {
        $usuarios = User::all();
        $segmentos = Segmento::all();

        return view('comunicaciones.create',compact('usuarios','segmentos'));
    }

    public function store(Request $request)
    {
        Comunicacion::create($request->all());

        return redirect()->route('comunicaciones.index');
    }
    public function dashboard($id)
        {

        $com = Comunicacion::with('destinatarios.user')->findOrFail($id);

        $total = $com->destinatarios->count();

        $leidos = $com->destinatarios
                    ->whereNotNull('leido_at')
                    ->count();

        $confirmados = $com->destinatarios
                        ->where('respuesta','confirmado')
                        ->count();

        $cancelados = $com->destinatarios
                        ->where('respuesta','cancelado')
                        ->count();

        return view('comunicaciones.dashboard',
        compact(
        'com',
        'total',
        'leidos',
        'confirmados',
        'cancelados'
        ));

        }
}
