<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Repository;

use Illuminate\Support\Facades\Auth;

class RepositoryController extends Controller
{

    public function index(Request $request){

        $aData = [
            'aRepositories' => $request->user()->repositories,
        ];
        return view('repositories.index', $aData);
    }

    public function show(Repository $repository){

        // Verificamos si el usuario logueado esta intentando actualizar un repositorio que no le pertenece
        // en cuyo caso devolvemos un estado HTTP 403
        if(Auth::user()->id != $repository->user_id){
            abort(403);
        }
        // devolvemos un status 200
        return view('repositories.show', compact('repository'));
    }

    public function create(){
        return view('repositories.create');
    }

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



    public function edit(Repository $repository){

        // Verificamos si el usuario logueado esta intentando actualizar un repositorio que no le pertenece
        // en cuyo caso devolvemos un estado HTTP 403
        if(Auth::user()->id != $repository->user_id){
            abort(403);
        }
        // devolvemos un status 200
        return view('repositories.edit', compact('repository'));
    }

    public function update(Request $request, Repository $repository){

        $request->validate([
            'url' => 'required|url',
            'description' => 'required'
        ]);

        // Verificamos si el usuario logueado esta intentando actualizar un repositorio que no le pertenece
        // en cuyo caso devolvemos un estado HTTP 403
        if(Auth::user()->id != $repository->user_id){
            abort(403);
        }

        // Debido a que ya tenemos un usuario logueado, podemos utilizarlo para crear un repositorio
        // que le pertenezca
        $repository->update($request->all());

        // Ahora, una vez que el repositorio esta creado, procedemos a realizar la redireccion
        return redirect()->route('repositories.edit', $repository);
    }

    public function destroy(Repository $repository){

        // Verificamos si el usuario logueado esta intentando actualizar un repositorio que no le pertenece
        // en cuyo caso devolvemos un estado HTTP 403
        if(Auth::user()->id != $repository->user_id){
            abort(403);
        }

        // Eliminamos el repositorio de la base de datos
        $repository->delete();
        // Redirigimos al usuario a la pantalla de lista de repositorios
        return redirect()->route('repositories.index');
    }
}
