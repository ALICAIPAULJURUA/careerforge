<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProfilePolicy
{
    public function view(User $user, Profile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    public function update(User $user, Profile $profile): bool
    {
        return $this->view($user, $profile);
    }

    public function delete(User $user, Profile $profile): bool
    {
        return $this->view($user, $profile);
    }
}
