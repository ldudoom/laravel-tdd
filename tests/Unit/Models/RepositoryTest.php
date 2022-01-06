<?php

namespace Tests\Unit\Models;

use App\Models\Repository;
use App\Models\User;
// En nuevas versiones de laravel debemos recordar reemplazar esta linea
//use PHPUnit\Framework\TestCase;
// por:
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user()
    {
        // Generamos un nuevo repositorio con la factory
        $repository = Repository::factory()->create();
        // Verificamos que la relacion este correcta verificando que el metodo user() de la clase Repository
        // de como resultado una instancia de la clase User
        $this->assertInstanceOf(User::class, $repository->user);
    }
}
