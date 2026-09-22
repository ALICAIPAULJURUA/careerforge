<?php

namespace App\Policies;

use App\Models\ResumeSection;
use App\Models\User;

class ResumeSectionPolicy
{
    public function view(User $user, ResumeSection $resumeSection): bool
    {
        return $resumeSection->resume->user_id === $user->id;
    }

    public function update(User $user, ResumeSection $resumeSection): bool
    {
        return $this->view($user, $resumeSection);
    }

    public function delete(User $user, ResumeSection $resumeSection): bool
    {
        return $this->view($user, $resumeSection);
    }
}
