<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Repository;

class RepositoryController extends Controller
{


    public function store(Request $request){

        $request->validate([
            'url' => 'required|url',
            'description' => 'required'
        ]);

        // Debido a que ya tenemos un usuario logueado, podemos utilizarlo para crear un repositorio
        // que le pertenezca
        $request->user()->repositories()->create($request->all());

        // Ahora, una vez que el repositorio esta creado, procedemos a realizar la redireccion
        return redirect()->route('repositories.index');
    }


    public function update(Request $request, Repository $repository){

        $request->validate([
            'url' => 'required|url',
            'description' => 'required'
        ]);

        // Debido a que ya tenemos un usuario logueado, podemos utilizarlo para crear un repositorio
        // que le pertenezca
        $repository->update($request->all());

        // Ahora, una vez que el repositorio esta creado, procedemos a realizar la redireccion
        return redirect()->route('repositories.edit', $repository);
    }
}
