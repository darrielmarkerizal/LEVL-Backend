<?php

namespace Modules\Content\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;
use Modules\Content\Exceptions\InvalidTransitionException;

interface ContentWorkflowServiceInterface
{
    
    public function transition(Model $content, string $newState, User $user, ?string $note = null): bool;

    
    public function canTransition(string $currentState, string $newState): bool;

    
    public function getAllowedTransitions(string $currentState): array;
}
