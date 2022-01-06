<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserTest extends TestCase
{

    public function test_has_many_repositories()
    {
        $oUser = new User;

        $this->assertInstanceOf(Collection::class, $oUser->repositories);
    }
}
