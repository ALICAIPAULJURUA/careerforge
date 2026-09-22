<?php

namespace App\Actions\Resume;

use App\Models\Resume;
use App\Models\User;

class CreateResumeAction
{
    public function __invoke(User $user, array $data): Resume
    {
        $resume = $user->resumes()->create($data);

        // Optionally create default sections lazily – not created here, toggled via UI.
        return $resume;
    }
}
