<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerPhoneDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $databaseFile = __DIR__ . '/../../database/database.sqlite';
        if (! file_exists($databaseFile)) {
            touch($databaseFile);
        }

        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'regexp_replace',
                fn (?string $value, string $pattern, string $replacement) => $value === null
                    ? null
                    : preg_replace('/' . $pattern . '/u', $replacement, $value),
                3
            );
        }

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
    }

    public function test_store_blocks_duplicate_phone_with_alphabetic_characters(): void
    {
        $user = User::factory()->create();

        Customer::create([
            'name' => 'Existing Customer',
            'phone' => '+57 300 111 2222 ext 123',
        ]);

        $response = $this->actingAs($user)
            ->from('/customers/create')
            ->post('/customers', [
                'name' => 'New Customer',
                'phone' => '573001112222123',
            ]);

        $response->assertRedirect('/customers/create');
        $response->assertSessionHas('duplicate_message');
        $this->assertEquals(1, Customer::count());
    }
}
