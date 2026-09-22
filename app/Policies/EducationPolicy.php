<?php

namespace App\Policies;

use App\Models\Education;
use App\Models\User;

class EducationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Education $education): bool
    {
        return $education->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Education $education): bool
    {
        return $this->view($user, $education);
    }

    public function delete(User $user, Education $education): bool
    {
        return $this->view($user, $education);
    }
}
