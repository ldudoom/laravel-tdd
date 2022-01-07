<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RepositoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /*
        En este test vamos a probar que la rutas del recurso repositories se encuentran efectivamente
        protegidas por una validacion de autenticacion, por lo que en todos los casos, un usuario invitado
        debera ser redirigido a la ruta del login en caso de que quiera acceder sin haber iniciado su sesion
     */
    public function test_guest(){
        $this->get('/repositories')->assertRedirect('login');           // index
        $this->get('/repositories/create')->assertRedirect('login');    // Create form
        $this->post('/repositories', [])->assertRedirect('login');      // Store
        $this->get('/repositories/1')->assertRedirect('login');         // Show
        $this->get('/repositories/1/edit')->assertRedirect('login');    // Edit
        $this->patch('/repositories/1')->assertRedirect('login');       // Update
        $this->delete('/repositories/1')->assertRedirect('login');      // Destroy
    }

    /*
        En este test vamos a probar que el guardado de un registro en la base de datos este correcto
        Para eso tendremos que validar que:
            - Se cargue correctamente un formulario
            - Ingresemos datos en el mismo
            - Enviemos al metodo store
            - Y esto persista en la BBDD
    */
    public function test_store(){
        // En primer lugar vamos a generar los datos que se van a enviar a guardar, es decir simulamos el formulario
        // de creacion de resgistros
        $aData = [
            'url' => $this->faker->url,
            'description' => $this->faker->text,
        ];

        // Ahora vamos a instanciar un usuario que sera el encargado de almacenar estos datos, y lo hacemos usando el factory
        $oUser = User::factory()->create();

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se haga
        // una redireccion hacia la lista de repositorios
        $this
            ->actingAs($oUser)
            ->post('/repositories', $aData)
            ->assertRedirect('/repositories');

        // Ahora verificamos que estos datos se encuentren registrados en la base de datos en la tabla repositories.
        $this->assertDatabaseHas('repositories', $aData);

    }
}
