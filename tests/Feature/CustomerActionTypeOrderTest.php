<?php

use App\Http\Controllers\CustomerController;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
        'permissions.customer_view_all_roles' => [1],
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_tag');
    Schema::dropIfExists('tags');
    Schema::dropIfExists('emails');
    Schema::dropIfExists('customer_histories');
    Schema::dropIfExists('actions');
    Schema::dropIfExists('action_types');
    Schema::dropIfExists('customer_statuses');
    Schema::dropIfExists('customers');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password')->nullable();
        $table->string('remember_token', 100)->nullable();
        $table->unsignedBigInteger('role_id')->nullable();
        $table->unsignedBigInteger('status_id')->nullable();
        $table->timestamps();
    });

    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('status_id')->nullable();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_statuses', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('description')->nullable();
        $table->integer('weight')->nullable();
        $table->timestamps();
    });

    Schema::create('action_types', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('description')->nullable();
        $table->integer('weigth')->nullable();
        $table->timestamps();
    });

    Schema::create('actions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->unsignedBigInteger('type_id');
        $table->timestamps();
    });

    Schema::create('customer_histories', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->timestamps();
    });

    Schema::create('emails', function (Blueprint $table) {
        $table->id();
        $table->integer('type_id')->nullable();
        $table->boolean('active')->nullable();
        $table->string('subject')->nullable();
        $table->string('view')->nullable();
        $table->timestamps();
    });

    Schema::create('tags', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    $user = User::factory()->create([
        'role_id' => 1,
        'status_id' => 1,
    ]);

    $this->actingAs($user);

    $customer = new Customer;
    $customer->user_id = $user->id;
    $customer->name = 'Cliente de prueba';
    $customer->save();

    DB::table('action_types')->insert([
        [
            'id' => 1,
            'name' => 'Accion tardia',
            'weigth' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'name' => 'Accion rapida',
            'weigth' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 3,
            'name' => 'Accion media',
            'weigth' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('actions')->insert([
        'id' => 1,
        'customer_id' => $customer->id,
        'type_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('customer_histories')->insert([
        'id' => 1,
        'customer_id' => $customer->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->customerId = $customer->id;
});

it('orders action types by weight in the scheduled action view', function () {
    $controller = app(CustomerController::class);
    $view = $controller->showAction($this->customerId, 1);

    $actionOptions = $view->getData()['action_options'];

    expect($actionOptions->pluck('id')->all())->toBe([2, 3, 1]);
});

it('orders action types by weight in the history view', function () {
    $controller = app(CustomerController::class);
    $view = $controller->showHistory(1);

    $actionOptions = $view->getData()['action_options'];

    expect($actionOptions->pluck('id')->all())->toBe([2, 3, 1]);
});
