<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RepositoryController extends Controller
{


    public function store(Request $request){
        // Debido a que ya tenemos un usuario logueado, podemos utilizarlo para crear un repositorio
        // que le pertenezca
        $request->user()->repositories()->create($request->all());

        // Ahora, una vez que el repositorio esta creado, procedemos a realizar la redireccion
        return redirect()->route('repositories.index');
    }
}
