<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\User;
use App\Models\Segmento;
use Illuminate\Http\Request;

class CampaniaController extends Controller
{
    public function index()
    {
        $campanias = Campania::with('responsable')->latest()->get();

        return view('campanias.index',compact('campanias'));
    }

    public function create()
    {
        $usuarios = User::all();
        $segmentos = Segmento::all();

        return view('campanias.create',compact('usuarios','segmentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {

        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'responsable_id' => 'required'
        ]);

        Campania::create([
            'titulo' => $request->nombre,
            'descripcion' => $request->descripcion,
            'responsable_id' => $request->responsable_id
        ]);

        return redirect()->route('campanias.index')
        ->with('success','Campaña creada correctamente');

    }
    /**
     * Display the specified resource.
     */
    public function show(Campania $campania)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campania $campania)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campania $campania)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campania $campania)
    {
        //
    }
}
