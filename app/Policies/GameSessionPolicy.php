<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    public function view(User $user, GameSession $session): bool
    {
        return $user->id === $session->created_by || $user->isAdmin();
    }

    public function manage(User $user, GameSession $session): bool
    {
        return $user->id === $session->created_by || $user->isAdmin();
    }

    public function start(User $user, GameSession $session): bool
    {
        return ($user->id === $session->created_by || $user->isAdmin())
            && $session->status === 'waiting'
            && $session->categories()->count() >= 1;
    }
}