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
        // En primer lugar generamos 10 repositorios que le van a pertenecer a cierto usuario
        $oUser1 = User::factory()->create();
        Repository::factory()->count(10)->create(['user_id' => $oUser1->id]);

        // Ahora generamos el usuario con el cual vamos a navegar en la lista de repositorios
        $oUser2 = User::factory()->create();

        // Ahora iniciamos la sesion del usuario con el que vamos a navegar
        // Accedemos a la ruta que hace referencia a la lista de repositorios
        // Esperamos en primer lugar un status 200 como respuesta
        // Y por ultimo, vamos a esperar recibir un texto indicando que el usuario no tiene repositorios
        $this
            ->actingAs($oUser2)
            ->get('repositories')
            ->assertStatus(200)
            ->assertSee('No tienes repositorios creados');
    }


    /*
        En este metodo vamos a validar que cuando el usuario acceda a la pantalla con la lista de repositorios
        unicamente vea los repositorios creados por el, y no los de otros usuarios.

        En este test vamos a crear varios repositorios de varios usuarios, y vamos a ingresar con otro
        usuario para el cual tambien vamos a crear repositorios, por lo que, si van a existir repositorios,
        entonces, el usuario con el que vamos a acceder deberia ver una lista unicamente de sus repositorios
    */
    public function test_index_with_repositories(){
        // En primer lugar generamos 10 repositorios que le van a pertenecer a cierto usuario
        Repository::factory()->count(10)->create();

        // Ahora generamos el usuario con el cual vamos a navegar en la lista de repositorios
        $oUser = User::factory()->create();
        $oRepository = Repository::factory()->create(['user_id' => $oUser->id]);

        // Ahora iniciamos la sesion del usuario con el que vamos a navegar
        // Accedemos a la ruta que hace referencia a la lista de repositorios
        // Esperamos en primer lugar un status 200 como respuesta
        // Y por ultimo, vamos a esperar poder ver el repositorio creado por el usuario
        $this
            ->actingAs($oUser)
            ->get('repositories')
            ->assertStatus(200)
            ->assertSee($oRepository->id)
            ->assertSee($oRepository->url);
    }


    /*
        En este test vamos a probar que un usuario unicamente pueda ver un repositorio si este le pertenece
    */
    public function test_show_repository(){
        // Creamos un usuario con el cual vamos a trabajar
        $oUser = User::factory()->create();
        // Luego creamos un repositorio que le pertenece al usuario
        $oRepository = Repository::factory()->create(['user_id' => $oUser->id]);

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Consultamos el detalle del repositorio, y debemos obtener un status 200
        // Unicamente probamos el estado HTTP ya que si se consulta un repositorio que no existe
        // o un repositorio que no le pertenece al usuario vamos a obtener un codigo diferente
        $this
            ->actingAs($oUser)
            ->get("/repositories/$oRepository->id")
            ->assertStatus(200);
    }


    /*
        En este test vamos a probar que visualizar un repositorio no pueda hacerse por un usuario que no es el dueño del repositorio
        Para eso tendremos que validar que:
            - crear un nuevo usuario e iniciar su sesion
            - crear un repositorio perteneciente a otro usuario
            - Enviemos al metodo show
            - validar que no se pueda visualizar recibiendo un estado HTTP 403
    */
    public function test_show_repository_policy(){


         // Ahora vamos a instanciar un usuario que sera el encargado de visualizar estos datos, y lo hacemos usando el factory
        $oUser = User::factory()->create();
        // Creamos un repositorio que no le pertenece a este usuario
        $oRepository = Repository::factory()->create();

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se haga
        // una redireccion hacia la lista de repositorios
        $this
            ->actingAs($oUser)
            ->get("/repositories/$oRepository->id")
            ->assertStatus(403); // Este estado es de proteccion, si recibimos este estado significa que el usuario no pudo hacer la actualizacion

    }



    /*
        Con este test vamos a validar que la vista de creacion de un repositorio se visualice correctamente
    */
    public function test_create_repository(){
        // Creamos un usuario con el cual vamos a trabajar
        $oUser = User::factory()->create();

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Consultamos el formulario de creacion del repositorio, y debemos obtener un status 200
        $this
            ->actingAs($oUser)
            ->get("/repositories/create")
            ->assertStatus(200);
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
        Con este test vamos a validar que la vista de edicion de un repositorio se visualice correctamente
    */
    public function test_edit_repository(){
        // Creamos un usuario con el cual vamos a trabajar
        $oUser = User::factory()->create();
        // Luego creamos un repositorio que le pertenece al usuario
        $oRepository = Repository::factory()->create(['user_id' => $oUser->id]);

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Consultamos el formulario de edicion del repositorio, y debemos obtener un status 200
        // tambien vamos a verificar que exista la informacion del repositorio, ya que debemos
        // asegurarnos de que el formulario se haya populado para su edicion
        $this
            ->actingAs($oUser)
            ->get("/repositories/$oRepository->id/edit")
            ->assertStatus(200)
            ->assertSee($oRepository->url)
            ->assertSee($oRepository->description);
    }


    /*

    */
    public function test_edit_repository_policy(){

         // Ahora vamos a instanciar un usuario que sera el encargado de editar estos datos, y lo hacemos usando el factory
        $oUser = User::factory()->create();
        // Creamos un repositorio que no le pertenece a este usuario
        $oRepository = Repository::factory()->create();

        // Ahora vamos a iniciar la sesion del usuario, ya que nuestras rutas estan protegidas
        // Enviamos los datos por post a guardar en la base de datos, y luego verificamos que se devuelva un status 403
        $this
            ->actingAs($oUser)
            ->get("/repositories/$oRepository->id/edit")
            ->assertStatus(403); // Este estado es de proteccion, si recibimos este estado significa que el usuario no pudo hacer la actualizacion

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
