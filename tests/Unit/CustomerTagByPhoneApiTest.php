<?php

use App\Models\Customer;
use App\Models\Tag;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_tag');
    Schema::dropIfExists('tags');
    Schema::dropIfExists('customers');

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('phone')->nullable();
        $table->string('phone2')->nullable();
        $table->string('contact_phone2')->nullable();
        $table->timestamps();
    });

    Schema::create('tags', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('name');
        $table->string('slug');
        $table->timestamps();
    });

    Schema::create('customer_tag', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id');
        $table->unsignedInteger('tag_id');
        $table->timestamps();
        $table->unique(['customer_id', 'tag_id']);
    });
});

it('attaches a tag to a customer by phone via api', function () {
    $customer = Customer::create([
        'phone' => '3001234567',
    ]);

    $tag = Tag::create([
        'name' => 'VIP',
        'slug' => 'vip',
    ]);

    $response = $this->postJson('/api/customers/update', [
        'phone' => '3001234567',
        'tag_id' => $tag->id,
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'customer_id' => $customer->id,
        'tag_id' => $tag->id,
        'attached' => true,
    ]);

    expect(
        DB::table('customer_tag')
            ->where('customer_id', $customer->id)
            ->where('tag_id', $tag->id)
            ->count()
    )->toBe(1);
});
