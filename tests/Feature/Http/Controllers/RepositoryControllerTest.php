<?php

namespace Tests\Feature\Http\Controllers;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Repository;

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
        En este metodo vamos a validar que cuando el usuario acceda a la pantalla con la lista de repositorios
        unicamente vea los repositorios creados por el, y no los de otros usuarios.

        Por lo que para este test vamos a crear varios repositorios de varios usuarios, y vamos a ingresar con otro
        usuario, por lo que, si van a existir repositorios, pero el usuario con el que vamos a acceder deberia ver
        una lista en blanco
    */
    public function test_index_empty_repositories(){

    }

    /*
        En este test vamos a probar que el guardado de un registro en la base de datos este correcto
        Para eso tendremos que validar que:
            - Se cargue correctamente un formulario
            - Ingresemos datos en el mismo
            - Enviemos al metodo store
            - Y esto persista en la BBDD
    */
    public function test_store_repository(){
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


    /*
        Vamos a verificar que se realice una validacion de la informacion que llega antes de ser guardada en la BBDD
    */
    public function test_store_repository_validation(){

        // Iniciamos un usuario para hacer el test
        $oUser = User::factory()->create();

        // Ahora enviamos un arreglo vacio de datos que el sistema no deberia permitir
        // Por lo tanto esperamos que el sistema haga una redireccion 302 que es una redireccion
        // a la misma pagina, pero esperamos que se hayan agregado los mensajes de error para los campos
        // uel y description, eso es lo que validamos en esta prueba
        $this
            ->actingAs($oUser)
            ->post('/repositories', [])
            ->assertStatus(302)
            ->assertSessionHasErrors('url', 'description');

    }


    /*
        En este test vamos a probar que el guardado de la actualizacionun registro en la base de datos este correcto
        Para eso tendremos que validar que:
            - Se cargue correctamente un formulario populado
            - Si queremos, actualizamos alguna informaicon
            - Enviemos al metodo update
            - Y estos cambios persistan en la BBDD
    */
    public function test_update_repository(){

        // Ahora vamos a instanciar un usuario que sera el encargado de almacenar estos datos, y lo hacemos usando el factory
        $oUser = User::factory()->create();

        $repository = Repository::factory()->create(['user_id' => $oUser->id]);
        // En primer lugar vamos a generar los datos que se van a enviar a guardar, es decir simulamos el formulario
        // de creacion de resgistros
        $aData = [
            'url' => $this->faker->url,
            'description' => $this->faker->text,
        ];

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se haga
        // una redireccion hacia la lista de repositorios
        $this
            ->actingAs($oUser)
            //->put("/repositories/$repository->id", $aData) // tambien funciona
            ->patch("/repositories/$repository->id", $aData)
            ->assertRedirect("/repositories/$repository->id/edit");

        // Ahora verificamos que estos datos se encuentren registrados en la base de datos en la tabla repositories.
        $this->assertDatabaseHas('repositories', $aData);

    }

    /*
        Vamos a verificar que se realice una validacion de la informacion que llega antes de ser guardada en la BBDD
    */
    public function test_update_repository_validation(){

        // Iniciamos un usuario para hacer el test
        $oUser = User::factory()->create();

        // Creamos el repositorio que, posteriormente, vamos a actualizar en el test
        $repository = Repository::factory()->create(['user_id' => $oUser->id]);

        // Ahora enviamos un arreglo vacio de datos que el sistema no deberia permitir
        // Por lo tanto esperamos que el sistema haga una redireccion 302 que es una redireccion
        // a la misma pagina, pero esperamos que se hayan agregado los mensajes de error para los campos
        // uel y description, eso es lo que validamos en esta prueba
        $this
            ->actingAs($oUser)
            //->put("/repositories/$repository->id", []) // tambien funciona
            ->patch("/repositories/$repository->id", [])
            ->assertStatus(302)
            ->assertSessionHasErrors('url', 'description');

    }


    /*
        En este test vamos a probar que el guardado de la actualizacionun registro en la base de datos no pueda hacerse
        por un usuario que no es el dueño del repositorio
        Para eso tendremos que validar que:
            - crear un nuevo usuario e iniciar su sesion
            - crear un repositorio perteneciente a otro usuario
            - Enviemos al metodo update
            - validar que no se pueda realizar la actualizacion recibiendo un estado HTTP 403
    */
    public function test_update_repository_policy(){

        /*
            Debido a que cuando se ejecutan los tests, se limpia la base de datos, cuando creemos un usuario nuevo
            para validar que el actualice solo sus repositorios, debemos tener en cuenta que:

            Cuando se ejecute el statement:  $oUser = User::factory()->create();

            El usuario tendra el ID -> 1

            Pero cuando se ejecute el statement:  $repository = Repository::factory()->create();

            Esta instruccion crea tambien un usuario junto con el repositorio, por lo que este
            repositorio tendra un user_id => 2

            Por lo que el usuario que creamos en la primera instruccion no deberia poder editar el repositorio
            de la segunda instruccion

        */
         // Ahora vamos a instanciar un usuario que sera el encargado de almacenar estos datos, y lo hacemos usando el factory
        $oUser = User::factory()->create();

        $repository = Repository::factory()->create();
        // En primer lugar vamos a generar los datos que se van a enviar a guardar, es decir simulamos el formulario
        // de creacion de resgistros
        $aData = [
            'url' => $this->faker->url,
            'description' => $this->faker->text,
        ];


        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se haga
        // una redireccion hacia la lista de repositorios
        $this
            ->actingAs($oUser)
            //->put("/repositories/$repository->id", $aData) // tambien funciona
            ->patch("/repositories/$repository->id", $aData)
            ->assertStatus(403); // Este estado es de proteccion, si recibimos este estado significa que el usuario no pudo hacer la actualizacion

    }

    /*
        En este test vamos a validar que se elimine un repositorio del sistema
    */
    public function test_destroy_repository(){

        // Ahora vamos a instanciar un usuario que sera el encargado de eliminar el repositorio, y lo hacemos usando el factory
        $oUser = User::factory()->create();

        // En primer lugar vamos a crear un repositorio para eliminarlo posteriormente
        $repository = Repository::factory()->create(['user_id' => $oUser->id]);



        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se haga
        // una redireccion hacia la lista de repositorios
        $this
            ->actingAs($oUser)
            ->delete("/repositories/$repository->id")
            ->assertRedirect('/repositories');

        // Ahora verificamos que estos datos se encuentren registrados en la base de datos en la tabla repositories.
        /*
            $this->assertDatabaseMissing('repositories', [
                'id' => $repository->id,
                'url' => $repository->url,
                'description' => $repository->description,
            ]);

            Se puede optimizar este codigo de esta manera
        */
        $this->assertDatabaseMissing('repositories', $repository->toArray());

    }


    /*
        En este test vamos a probar que la eliminacion de un repositorio en la base de datos no pueda hacerse
        por un usuario que no es el dueño del repositorio
        Para eso tendremos que validar que:
            - crear un nuevo usuario e iniciar su sesion
            - crear un repositorio perteneciente a otro usuario
            - Enviemos al metodo destroy
            - validar que no se pueda realizar la actualizacion recibiendo un estado HTTP 403
    */
    public function test_destroy_repository_policy(){

        /*
            Debido a que cuando se ejecutan los tests, se limpia la base de datos, cuando creemos un usuario nuevo
            para validar que el actualice solo sus repositorios, debemos tener en cuenta que:

            Cuando se ejecute el statement:  $oUser = User::factory()->create();

            El usuario tendra el ID -> 1

            Pero cuando se ejecute el statement:  $repository = Repository::factory()->create();

            Esta instruccion crea tambien un usuario junto con el repositorio, por lo que este
            repositorio tendra un user_id => 2

            Por lo que el usuario que creamos en la primera instruccion no deberia poder editar el repositorio
            de la segunda instruccion

        */
        $oUser = User::factory()->create();

        $repository = Repository::factory()->create();


        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se haga
        // una redireccion hacia la lista de repositorios
        $this
            ->actingAs($oUser)
            //->put("/repositories/$repository->id", $aData) // tambien funciona
            ->delete("/repositories/$repository->id")
            ->assertStatus(403); // Este estado es de proteccion, si recibimos este estado significa que el usuario no pudo hacer la actualizacion

    }
}
