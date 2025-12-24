<?php

use App\Services\CustomerService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

function prepareCustomerSchema(): void
{
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_tag');
    Schema::dropIfExists('tags');
    Schema::dropIfExists('users');
    Schema::dropIfExists('customer_statuses');
    Schema::dropIfExists('customers');

    Schema::create('customer_statuses', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->integer('weight')->nullable();
        $table->string('color')->nullable();
    });

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('tags', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_tag', function (Blueprint $table): void {
        $table->unsignedBigInteger('customer_id');
        $table->unsignedBigInteger('tag_id');
        $table->timestamps();
    });

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('document')->nullable();
        $table->string('city')->nullable();
        $table->string('department')->nullable();
        $table->string('country')->nullable();
        $table->string('ad_name')->nullable();
        $table->string('adset_name')->nullable();
        $table->string('campaign_name')->nullable();
        $table->string('business')->nullable();
        $table->text('notes')->nullable();
        $table->string('contact_name')->nullable();
        $table->string('phone_last9')->nullable();
        $table->string('phone2_last9')->nullable();
        $table->string('contact_phone2_last9')->nullable();
        $table->string('phone')->nullable();
        $table->string('phone2')->nullable();
        $table->string('contact_phone2')->nullable();
        $table->string('email')->nullable();
        $table->string('contact_email')->nullable();
        $table->string('scoring_interest')->nullable();
        $table->string('scoring_profile')->nullable();
        $table->string('maker')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('source_id')->nullable();
        $table->unsignedBigInteger('status_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('inquiry_product_id')->nullable();
        $table->timestamps();
    });
}

it('searches customers by contact name', function () {
    prepareCustomerSchema();

    DB::table('customers')->insert([
        'name' => 'Empresa Demo',
        'contact_name' => 'Carlos Gomez',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new CustomerService;
    $request = Request::create('/customers', 'GET', ['search' => 'Carlos']);

    $results = $service->filterCustomers($request, collect(), null, false, 0, true);

    expect($results)->toHaveCount(1);
    expect($results->first()->contact_name)->toBe('Carlos Gomez');
});

it('filters customers without a status when sin_estado is selected', function () {
    prepareCustomerSchema();

    DB::table('customer_statuses')->insert([
        'id' => 1,
        'name' => 'Activo',
        'weight' => 1,
        'color' => '#ffffff',
    ]);

    DB::table('customers')->insert([
        [
            'name' => 'Cliente asignado',
            'status_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Cliente sin estado',
            'status_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $service = new CustomerService;
    $request = Request::create('/customers', 'GET', [
        'status_id' => CustomerService::STATUS_FILTER_UNASSIGNED,
    ]);

    $results = $service->filterCustomers($request, collect(), null, false, 0, true);

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Cliente sin estado');
    expect($results->first()->status_id)->toBeNull();
});
