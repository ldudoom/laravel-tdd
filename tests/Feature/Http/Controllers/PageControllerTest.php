<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Repository;

class PageControllerTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /*
        Vamos a validar que el area publica del sitio funcione correctamente
        En este caso vamos a validar que el home se cargue correctamente con la lista de repositorios que
        existan en la BBDD
        Aqui no es necesario que un usuario este logueado, debe poder acceder sin problema al home y ver la informacion
     */
    public function test_public_home_page()
    {
        // En primer lugar creamos un repositorio que podraverse en el home del sitio
        $oRepository = Repository::factory()->create();

        // A continuacion validamos que el home nos devuelva un status 200
        // y que ademas este presente el repositorio que hemos creado
        $this
            ->get('/')
            ->assertStatus(200)
            ->assertSee($oRepository->url);
    }
}
