<?php

use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerFile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

$databaseFile = __DIR__.'/../../database/database.sqlite';
if (! file_exists($databaseFile)) {
    touch($databaseFile);
}

putenv('DB_CONNECTION=sqlite');
putenv("DB_DATABASE={$databaseFile}");
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $databaseFile;
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = $databaseFile;

uses(TestCase::class);

beforeEach(function () use ($databaseFile) {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $databaseFile,
        'permissions.customer_view_all_roles' => [1],
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');
    DB::setDefaultConnection('sqlite');

    Storage::fake('spaces');

    Schema::connection('sqlite')->dropIfExists('actions');
    Schema::connection('sqlite')->dropIfExists('customer_files');
    Schema::connection('sqlite')->dropIfExists('customers');
    Schema::connection('sqlite')->dropIfExists('user_statuses');
    Schema::connection('sqlite')->dropIfExists('users');

    Schema::connection('sqlite')->create('users', function (Blueprint $table) {
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

    Schema::connection('sqlite')->create('user_statuses', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::connection('sqlite')->create('customers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('status_id')->nullable();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::connection('sqlite')->create('actions', function (Blueprint $table) {
        $table->id();
        $table->text('note')->nullable();
        $table->unsignedBigInteger('type_id')->nullable();
        $table->unsignedBigInteger('creator_user_id')->nullable();
        $table->unsignedBigInteger('customer_owner_id')->nullable();
        $table->timestamp('customer_createad_at')->nullable();
        $table->timestamp('customer_updated_at')->nullable();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->timestamp('due_date')->nullable();
        $table->timestamp('delivery_date')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::connection('sqlite')->create('customer_files', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('url')->nullable();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('creator_user_id')->nullable();
        $table->timestamps();
    });

    DB::table('user_statuses')->insert([
        'id' => 1,
        'name' => 'Activo',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('stores an uploaded file when creating a customer action', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'status_id' => 1,
    ]);

    $customer = Customer::create([
        'name' => 'Cliente de prueba',
        'user_id' => $user->id,
    ]);

    $file = UploadedFile::fake()->create('factura.pdf', 12, 'application/pdf');

    $response = $this->actingAs($user)->post("/customers/{$customer->id}/action/store", [
        'note' => 'Nota de seguimiento',
        'type_id' => 1,
        'customer_id' => $customer->id,
        'file' => $file,
    ]);

    $response->assertStatus(302);
    expect(Action::query()->count())->toBe(1);

    $customerFile = CustomerFile::query()->first();
    expect($customerFile)->not->toBeNull();
    expect($customerFile->name)->toBe('factura.pdf');

    Storage::disk('spaces')->assertExists("files/{$customer->id}/{$customerFile->url}");
});
