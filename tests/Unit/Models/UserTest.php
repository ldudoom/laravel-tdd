<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserTest extends TestCase
{

    public function test_has_many_repositories()
    {
        // Creamos una nueva instancia de User
        $oUser = new User;
        // Verificamos que la relacion este correcta, verificando que el metodo repositories de la clase User genere una
        // coleccion de la clase Repository
        $this->assertInstanceOf(Collection::class, $oUser->repositories);
    }
}
