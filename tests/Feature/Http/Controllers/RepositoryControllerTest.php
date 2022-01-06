<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RepositoryControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_guest()
    {
        $this->get('/repositories')->assertRedirect('login');           // index
        $this->get('/repositories/create')->assertRedirect('login');    // Create form
        $this->post('/repositories', [])->assertRedirect('login');      // Store
        $this->get('/repositories/1')->assertRedirect('login');         // Show
        $this->get('/repositories/1/edit')->assertRedirect('login');    // Edit
        $this->patch('/repositories/1')->assertRedirect('login');       // Update
        $this->delete('/repositories/1')->assertRedirect('login');      // Destroy
    }
}
