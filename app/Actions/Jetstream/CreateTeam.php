<?php

declare(strict_types=1);

namespace App\Actions\Jetstream;

use App\Models\{Team, User};
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Events\AddingTeam;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Illuminate\Support\Facades\{Gate, Validator};

final class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array{name: string}  $input
     */
    public function create(User $user, array $input): Team
    {
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        /** @var Team $team */
        $team = $user->ownedTeams()->create([
            'name' => $input['name'],
            'personal_team' => false,
        ]);
        $user->switchTeam($team);

        return $team;
    }
}
