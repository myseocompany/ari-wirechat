<?php

use App\Models\Customer;
use App\Models\CustomerFile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Storage::fake('spaces');

    Schema::dropIfExists('users');
    Schema::dropIfExists('customer_files');
    Schema::dropIfExists('customers');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_files', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('url')->nullable();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('creator_user_id')->nullable();
        $table->timestamps();
    });
});

it('stores duplicate filenames without overwriting other uploads', function () {
    $user = User::factory()->create();
    $customer = Customer::create(['name' => 'Cliente Uno']);

    $fileA = UploadedFile::fake()->create('claudia.jpg', 10, 'image/jpeg');
    $fileB = UploadedFile::fake()->create('claudia.jpg', 12, 'image/jpeg');

    $response = $this->actingAs($user)->postJson(route('customer_files.store'), [
        'customer_id' => $customer->id,
        'files' => [$fileA, $fileB],
    ]);

    $response->assertOk()->assertJson(['ok' => true]);
    $files = $response->json('files');

    expect($files)->toHaveCount(2);
    expect($files[0]['name'])->toBe('claudia.jpg');
    expect($files[0]['url'])->not->toBe($files[1]['url']);

    Storage::disk('spaces')->assertExists("files/{$customer->id}/{$files[0]['url']}");
    Storage::disk('spaces')->assertExists("files/{$customer->id}/{$files[1]['url']}");

    $fileToDelete = CustomerFile::find($files[0]['id']);

    $deleteResponse = $this->actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->delete(route('customer_files.destroy', $fileToDelete));

    $deleteResponse->assertOk()->assertJson(['ok' => true]);

    Storage::disk('spaces')->assertMissing("files/{$customer->id}/{$files[0]['url']}");
    Storage::disk('spaces')->assertExists("files/{$customer->id}/{$files[1]['url']}");

    expect(CustomerFile::whereKey($files[1]['id'])->exists())->toBeTrue();
});
