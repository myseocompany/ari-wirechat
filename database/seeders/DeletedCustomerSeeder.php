<?php

namespace Database\Seeders;

use App\Models\DeletedCustomer;
use Illuminate\Database\Seeder;

class DeletedCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeletedCustomer::factory()->count(25)->create();
    }
}
