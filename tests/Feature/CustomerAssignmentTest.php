<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $databaseFile = __DIR__ . '/../../database/database.sqlite';
        if (! file_exists($databaseFile)) {
            touch($databaseFile);
        }

        parent::setUp();

        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable();
            });
        }

        if (! Schema::hasColumn('users', 'status_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('status_id')->nullable();
            });
        }

        Schema::dropIfExists('customer_histories');
        Schema::dropIfExists('customers');

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('document')->nullable();
            $table->string('position')->nullable();
            $table->string('business')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->integer('count_empanadas')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('department')->nullable();
            $table->string('bought_products')->nullable();
            $table->decimal('total_sold', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->boolean('technical_visit')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone2')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_position')->nullable();
            $table->string('scoring_interest')->nullable();
            $table->string('scoring_profile')->nullable();
            $table->string('rd_public_url')->nullable();
            $table->string('empanadas_size')->nullable();
            $table->integer('number_venues')->nullable();
            $table->unsignedBigInteger('updated_user_id')->nullable();
            $table->unsignedBigInteger('creator_user_id')->nullable();
            $table->string('maker')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('name')->nullable();
            $table->string('document')->nullable();
            $table->string('position')->nullable();
            $table->string('business')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('department')->nullable();
            $table->string('bought_products')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->boolean('technical_visit')->nullable();
            $table->unsignedBigInteger('updated_user_id')->nullable();
            $table->timestamps();
        });
    }

    private function createCustomerOwnedBy(User $owner): Customer
    {
        $customer = new Customer();
        $customer->name = 'Test Customer';
        $customer->status_id = 1;
        $customer->user_id = $owner->id;
        $customer->save();

        return $customer->fresh();
    }

    public function test_ajax_update_is_forbidden_for_user_without_permission(): void
    {
        $owner = User::factory()->create(['role_id' => 1]);
        $target = User::factory()->create(['role_id' => 1]);
        $unauthorized = User::factory()->create(['role_id' => 2]);

        $customer = $this->createCustomerOwnedBy($owner);

        $response = $this->actingAs($unauthorized)
            ->get('/customers/ajax/update_user?customer_id=' . $customer->id . '&user_id=' . $target->id);

        $response->assertForbidden();
        $this->assertSame($owner->id, $customer->fresh()->user_id);
    }

    public function test_update_route_is_forbidden_for_user_without_permission(): void
    {
        $owner = User::factory()->create(['role_id' => 1]);
        $target = User::factory()->create(['role_id' => 1]);
        $unauthorized = User::factory()->create(['role_id' => 2]);

        $customer = $this->createCustomerOwnedBy($owner);

        $response = $this->actingAs($unauthorized)->post("/customers/{$customer->id}/update", [
            'name' => 'Updated Name',
            'phone' => '123456789',
            'user_id' => $target->id,
        ]);

        $response->assertForbidden();
        $this->assertSame($owner->id, $customer->fresh()->user_id);
    }

    public function test_authorized_user_can_reassign_via_ajax(): void
    {
        $authorized = User::factory()->create(['role_id' => 1]);
        $target = User::factory()->create(['role_id' => 15]);
        $customer = $this->createCustomerOwnedBy($authorized);

        $response = $this->actingAs($authorized)
            ->get('/customers/ajax/update_user?customer_id=' . $customer->id . '&user_id=' . $target->id);

        $response->assertOk();
        $this->assertSame($target->id, $customer->fresh()->user_id);
    }

    public function test_authorized_user_can_reassign_via_update_route(): void
    {
        $authorized = User::factory()->create(['role_id' => 1]);
        $target = User::factory()->create(['role_id' => 15]);
        $customer = $this->createCustomerOwnedBy($authorized);

        $response = $this->actingAs($authorized)->post("/customers/{$customer->id}/update", [
            'name' => 'Updated Name',
            'phone' => '123456789',
            'user_id' => $target->id,
        ]);

        $response->assertRedirect("/customers/{$customer->id}/show");
        $this->assertSame($target->id, $customer->fresh()->user_id);
    }
}
