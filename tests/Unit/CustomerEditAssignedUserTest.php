<?php

use App\Http\Controllers\CustomerController;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

it('appends assigned user when not in active list', function () {
    $controller = new class(new CustomerService) extends CustomerController
    {
        public function appendAssignedUserForTest(Collection $users, ?User $assignedUser): Collection
        {
            return $this->appendAssignedUser($users, $assignedUser);
        }
    };

    $activeUser = User::make(['name' => 'Activo', 'status_id' => 1]);
    $activeUser->id = 1;
    $assignedUser = User::make(['name' => 'Inactivo', 'status_id' => 0]);
    $assignedUser->id = 99;

    $users = collect([$activeUser]);
    $result = $controller->appendAssignedUserForTest($users, $assignedUser);

    expect($result->pluck('id')->all())->toBe([1, 99]);
});
