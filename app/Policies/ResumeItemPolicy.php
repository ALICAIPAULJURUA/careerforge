<?php

namespace App\Policies;

use App\Models\ResumeItem;
use App\Models\User;

class ResumeItemPolicy
{
    public function view(User $user, ResumeItem $resumeItem): bool
    {
        return $resumeItem->resumeSection->resume->user_id === $user->id;
    }

    public function update(User $user, ResumeItem $resumeItem): bool
    {
        return $this->view($user, $resumeItem);
    }

    public function delete(User $user, ResumeItem $resumeItem): bool
    {
        return $this->view($user, $resumeItem);
    }
}
