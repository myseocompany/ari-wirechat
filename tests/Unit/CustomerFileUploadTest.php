<?php

use App\Models\Customer;
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
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::connection('sqlite')->dropIfExists('customer_files');
    Schema::connection('sqlite')->dropIfExists('customers');

    Schema::connection('sqlite')->create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::connection('sqlite')->create('customer_files', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->string('url');
        $table->unsignedBigInteger('creator_user_id')->nullable();
        $table->timestamps();
    });
});

it('uploads customer files via ajax without page reload', function () {
    Storage::fake('spaces');

    $user = User::factory()->create();
    $customer = Customer::create(['name' => 'Test Customer']);
    $file = UploadedFile::fake()->create('doc.txt', 5, 'text/plain');

    $response = $this->actingAs($user)->postJson('/customer_files', [
        'customer_id' => $customer->id,
        'files' => [$file],
    ]);

    $response->assertSuccessful()
        ->assertJson(['ok' => true]);

    $this->assertDatabaseHas('customer_files', [
        'customer_id' => $customer->id,
        'url' => 'doc.txt',
    ]);

    Storage::disk('spaces')->assertExists("files/{$customer->id}/doc.txt");
});
