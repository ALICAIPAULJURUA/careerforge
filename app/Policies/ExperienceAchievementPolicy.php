<?php

namespace App\Policies;

use App\Models\ExperienceAchievement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExperienceAchievementPolicy
{
    public function view(User $user, ExperienceAchievement $experienceAchievement): bool
    {
        return $experienceAchievement->experience->user_id === $user->id;
    }

    public function update(User $user, ExperienceAchievement $experienceAchievement): bool
    {
        return $this->view($user, $experienceAchievement);
    }

    public function delete(User $user, ExperienceAchievement $experienceAchievement): bool
    {
        return $this->view($user, $experienceAchievement);
    }
}
