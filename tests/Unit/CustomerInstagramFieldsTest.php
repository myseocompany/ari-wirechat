<?php

use App\Models\Customer;
use Tests\TestCase;

uses(TestCase::class);

function instagramMigrationSource(): string
{
    $path = database_path('migrations/2026_10_25_120000_add_instagram_fields_to_customers_table.php');

    expect(file_exists($path))->toBeTrue();

    return file_get_contents($path);
}

it('guards the migration so it can run on a base where the column already exists', function () {
    $migration = instagramMigrationSource();

    expect($migration)->toContain("Schema::hasTable('customers')");
    expect($migration)->toContain("Schema::hasColumn('customers', 'instagram_url')");
    expect($migration)->toContain("Schema::hasColumn('customers', 'instagram_followers')");
});

it('declares both instagram columns as nullable and without indexes', function () {
    $migration = instagramMigrationSource();

    expect($migration)->toContain("\$table->string('instagram_url', 255)->nullable()");
    expect($migration)->toContain("\$table->unsignedBigInteger('instagram_followers')->nullable()");
    expect($migration)->not->toContain('->index()');
    expect($migration)->not->toContain('->default(');
});

it('drops only the existing columns on rollback', function () {
    $migration = instagramMigrationSource();

    $downPos = strpos($migration, 'public function down(): void');

    expect($downPos)->not->toBeFalse();
    expect(substr($migration, $downPos))->toContain('dropColumn($column)');
});

it('exposes the instagram fields on the customer model', function () {
    $customer = new Customer;

    expect($customer->getFillable())->toContain('instagram_url');
    expect($customer->getFillable())->toContain('instagram_followers');
    expect($customer->getCasts())->toHaveKey('instagram_followers');
    expect($customer->getCasts()['instagram_followers'])->toBe('integer');
});

it('normalizes the stored instagram value into a canonical profile url', function (string $stored) {
    $customer = new Customer;
    $customer->instagram_url = $stored;

    expect($customer->getInstagramHandle())->toBe('maquiempanadas');
    expect($customer->getInstagramUrl())->toBe('https://instagram.com/maquiempanadas');
})->with([
    'maquiempanadas',
    '@maquiempanadas',
    '  maquiempanadas  ',
    'instagram.com/maquiempanadas',
    'https://instagram.com/maquiempanadas',
    'https://www.instagram.com/maquiempanadas/',
    'https://www.instagram.com/maquiempanadas/?igsh=abc123',
]);

it('returns null instagram accessors when there is no value stored', function () {
    $customer = new Customer;

    expect($customer->getInstagramHandle())->toBeNull();
    expect($customer->getInstagramUrl())->toBeNull();

    $customer->instagram_url = '   ';

    expect($customer->getInstagramHandle())->toBeNull();
    expect($customer->getInstagramUrl())->toBeNull();
});

it('casts the followers count to an integer', function () {
    $customer = new Customer;
    $customer->instagram_followers = '84200';

    expect($customer->instagram_followers)->toBe(84200);
});

it('shows the instagram link and followers inside the details block', function () {
    $blade = file_get_contents(resource_path('views/customers/show.blade.php'));

    $detailsPos = strpos($blade, '>Detalles<');
    $addressPos = strpos($blade, '>Dirección<');
    $instagramPos = strpos($blade, '>Instagram<', $detailsPos);

    expect($instagramPos)->not->toBeFalse();
    expect($instagramPos)->toBeLessThan($addressPos);

    expect($blade)->toContain('$instagramUrl = $model->getInstagramUrl()');
    expect($blade)->toContain('@if($instagramUrl || !is_null($model->instagram_followers))');
    expect($blade)->toContain('rel="noopener"');
    expect($blade)->toContain("number_format(\$model->instagram_followers, 0, ',', '.')");
});

it('lets the edit form submit both instagram fields', function () {
    $blade = file_get_contents(resource_path('views/customers/edit.blade.php'));

    expect($blade)->toContain('name="instagram_url"');
    expect($blade)->toContain('name="instagram_followers"');
});

it('persists both instagram fields on customer update', function () {
    $controller = file_get_contents(app_path('Http/Controllers/CustomerController.php'));

    $updatePos = strpos($controller, 'public function update(Request $request, $id)');

    expect($updatePos)->not->toBeFalse();

    $updateBody = substr($controller, $updatePos, 4000);

    expect($updateBody)->toContain("\$model->instagram_url = \$request->filled('instagram_url')");
    expect($updateBody)->toContain("\$model->instagram_followers = \$request->filled('instagram_followers')");
    expect($updateBody)->toContain(': null;');
});
