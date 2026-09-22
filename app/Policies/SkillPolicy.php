<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Skill $skill): bool
    {
        return $skill->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Skill $skill): bool
    {
        return $this->view($user, $skill);
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $this->view($user, $skill);
    }
}
