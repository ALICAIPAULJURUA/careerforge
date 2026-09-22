<?php

namespace App\Policies;

use App\Models\ProjectTechnology;
use App\Models\User;

class ProjectTechnologyPolicy
{
    public function view(User $user, ProjectTechnology $projectTechnology): bool
    {
        return $projectTechnology->project->user_id === $user->id;
    }

    public function update(User $user, ProjectTechnology $projectTechnology): bool
    {
        return $this->view($user, $projectTechnology);
    }

    public function delete(User $user, ProjectTechnology $projectTechnology): bool
    {
        return $this->view($user, $projectTechnology);
    }
}
