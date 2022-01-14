<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Repository;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepositoryPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function pass(User $oUser, Repository $oRepository){
        return $oUser->id == $oRepository->user_id;
    }
}
