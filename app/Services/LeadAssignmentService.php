<?php

namespace App\Services;

use App\Models\LeadAssignmentLog;
use App\Models\User;

class LeadAssignmentService
{
    /**
     * Selects the next assignable user in sequence.
     */
    public function getNextUserId(): ?int
    {
        $lastAssignedUser = User::where('last_assigned', 1)->first();

        if ($lastAssignedUser) {
            $lastAssignedUser->last_assigned = 0;
            $lastAssignedUser->save();
        }

        $baseQuery = User::query()
            ->where('status_id', 1)
            ->where('assignable', '>', 0);

        $nextUser = (clone $baseQuery)
            ->when($lastAssignedUser, fn ($query) => $query->where('id', '>', $lastAssignedUser->id))
            ->first();

        if (! $nextUser) {
            $nextUser = $baseQuery->first();
        }

        if (! $nextUser) {
            return null;
        }

        $nextUser->last_assigned = 1;
        $nextUser->save();

        return $nextUser->id;
    }

    /**
     * Returns an assignable user based on the configured mode.
     */
    public function getAssignableUserId(?string $mode = null): ?int
    {
        $mode = $mode ?: config('lead_assignment.mode', 'sequential');

        return $mode === 'random'
            ? $this->getRandomNextUserId()
            : $this->getNextUserId();
    }

    /**
     * Selects an assignable user randomly but honoring the "assignable" weight.
     */
    public function getRandomNextUserId(): ?int
    {
        $users = User::query()
            ->where('status_id', 1)
            ->where('assignable', '>', 0)
            ->get(['id', 'assignable']);

        if ($users->isEmpty()) {
            return null;
        }

        $weightedUsers = $users
            ->mapWithKeys(fn ($user) => [$user->id => max((int) $user->assignable, 1)])
            ->all();

        $selectedUserId = $this->weightedRandomSelection($weightedUsers);

        if (! $selectedUserId) {
            return null;
        }

        User::query()->update(['last_assigned' => 0]);
        User::where('id', $selectedUserId)->update(['last_assigned' => 1]);

        return $selectedUserId;
    }

    /**
     * Persists a log entry for an assignment event.
     */
    public function recordAssignment(?int $userId, ?int $customerId, string $context, array $meta = []): void
    {
        if (! $userId) {
            return;
        }

        LeadAssignmentLog::create([
            'user_id' => $userId,
            'customer_id' => $customerId,
            'context' => $context,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * Weighted random selection helper.
     *
     * @param  array<int, int>  $weights
     */
    protected function weightedRandomSelection(array $weights): ?int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) {
            return null;
        }

        $rand = mt_rand(1, $totalWeight);
        foreach ($weights as $id => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return (int) $id;
            }
        }

        return null;
    }
}
