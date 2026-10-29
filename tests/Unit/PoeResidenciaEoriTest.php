<?php

use App\Models\CustomerMetaData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

const POE_EORI_NOTICE = 'al residir en la Unión Europea o el Reino Unido, es posible que necesite contar con un número EORI';

function poeEoriMigration(): object
{
    return require database_path('migrations/2026_10_29_120000_add_poe_residencia_ue_eori_metadata.php');
}

function seedPoeCampaign(int $campaignId = 40): void
{
    DB::table('campaigns')->insert(['id' => $campaignId, 'name' => 'POE']);

    $rows = [
        [14, null, 'Información de la compañía', null],
        [15, 14, 'Nombre completo de la compañía', 1],
        [20, null, 'Información de contacto', null],
        [22, 20, 'Nombre completo', 1],
        [28, null, 'Información de destino', null],
        [32, 28, 'País', 1],
        [34, null, 'Veracidad de la información', null],
        [27, 34, 'Declaro que la información que se suministra es veraz', 3],
    ];

    foreach ($rows as [$id, $parentId, $value, $typeId]) {
        DB::table('customer_meta_datas')->insert(['id' => $id, 'parent_id' => $parentId, 'value' => $value, 'type_id' => $typeId]);
    }

    // Orden de inserción del pivote distinto al deseado, para probar el orden por weight
    foreach ([34, 28, 20, 14] as $groupId) {
        DB::table('campaign_customer_meta_data')->insert(['campaign_id' => $campaignId, 'customer_meta_data_id' => $groupId]);
    }
}

beforeEach(function () {
    config([
        'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('campaigns', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_meta_datas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->text('value')->nullable();
        $table->unsignedBigInteger('type_id')->nullable();
        $table->integer('weight')->nullable();
        $table->timestamps();
    });

    Schema::create('campaign_customer_meta_data', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('campaign_id');
        $table->unsignedBigInteger('customer_meta_data_id');
        $table->timestamps();
    });

    Schema::create('customer_metas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->unsignedBigInteger('meta_data_id')->nullable();
        $table->unsignedBigInteger('audience_id')->nullable();
        $table->text('value')->nullable();
        $table->timestamps();
    });

    DB::table('customers')->insert(['id' => 677105, 'name' => 'Cliente de prueba']);
    seedPoeCampaign();
    poeEoriMigration()->up();
});

it('adds the residence group with its questions and options to campaign 40', function () {
    expect(DB::table('customer_meta_datas')->whereBetween('id', [4000, 4006])->count())->toBe(7);
    expect(DB::table('campaign_customer_meta_data')->where('campaign_id', 40)->where('customer_meta_data_id', 4000)->count())->toBe(1);
    expect(DB::table('customer_meta_datas')->where('id', 4001)->value('value'))->toBe('¿Es usted residente de la Unión Europea o Reino Unido?');
    expect(DB::table('customer_meta_datas')->where('parent_id', 4002)->orderBy('weight')->pluck('value')->all())
        ->toBe(['Sí, cuento con número EORI.', 'No, aún no cuento con número EORI.']);
});

it('is idempotent when the migration runs twice', function () {
    poeEoriMigration()->up();

    expect(DB::table('customer_meta_datas')->whereBetween('id', [4000, 4006])->count())->toBe(7);
    expect(DB::table('campaign_customer_meta_data')->where('customer_meta_data_id', 4000)->count())->toBe(1);
});

it('rolls back the questions without deleting customer answers', function () {
    DB::table('customer_metas')->insert(['customer_id' => 677105, 'meta_data_id' => 4001, 'value' => 'Sí']);

    poeEoriMigration()->down();

    expect(DB::table('customer_meta_datas')->whereBetween('id', [4000, 4006])->count())->toBe(0);
    expect(DB::table('campaign_customer_meta_data')->where('customer_meta_data_id', 4000)->count())->toBe(0);
    expect(DB::table('customer_meta_datas')->whereIn('id', [14, 20, 28, 34])->whereNotNull('weight')->count())->toBe(0);
    expect(DB::table('customer_metas')->where('meta_data_id', 4001)->count())->toBe(1);
});

it('AC1: renders the mandatory residence question with exactly two options', function () {
    $html = $this->get('/metadata/677105/create/poe/40')->assertOk()->getContent();

    expect($html)->toContain('¿Es usted residente de la Unión Europea o Reino Unido?');
    expect(substr_count($html, 'name="meta_4001"'))->toBe(2);
    expect($html)->toMatch('/class="form-check-input poe-eu-resident" type="radio" required\s+name="meta_4001"[^>]*value="Sí"/');
    expect($html)->toMatch('/name="meta_4001"[^>]*value="No"/');
});

it('AC3/AC4: renders the EORI notice twice, the EORI question and its options disabled by default', function () {
    $html = $this->get('/metadata/677105/create/poe/40')->assertOk()->getContent();

    expect($html)->toContain('Importante: número EORI');
    expect(substr_count($html, POE_EORI_NOTICE))->toBe(2);
    expect($html)->not->toContain('al residir en Europa');
    expect($html)->toContain('¿Cuenta usted con un número EORI?');
    expect($html)->toMatch('/type="radio" required disabled\s+name="meta_4002"[^>]*value="Sí, cuento con número EORI\."/');
    expect($html)->toMatch('/type="radio" required disabled\s+name="meta_4002"[^>]*value="No, aún no cuento con número EORI\."/');
    expect($html)->toContain('id="poe_eori_reminder" style="display:none"');
});

it('places the residence block between destination and veracity', function () {
    $html = $this->get('/metadata/677105/create/poe/40')->assertOk()->getContent();

    $positions = array_map(fn ($text) => strpos($html, $text), [
        'Información de la compañía',
        'Información de contacto',
        'Información de destino',
        'Residencia y número EORI',
        'Veracidad de la información',
    ]);

    expect($positions)->not->toContain(false);
    expect($positions)->toBe(collect($positions)->sort()->values()->all());
});

it('does not show the residence block for other campaigns using the POE form', function () {
    DB::table('campaigns')->insert(['id' => 41, 'name' => 'Otra']);
    DB::table('campaign_customer_meta_data')->insert(['campaign_id' => 41, 'customer_meta_data_id' => 28]);

    $html = $this->get('/metadata/677105/create/poe/41')->assertOk()->getContent();

    expect($html)->toContain('Información de destino');
    expect($html)->not->toContain('name="meta_4001"');
});

it('AC7/AC8: stores residence and EORI answers even without EORI and sends nothing', function () {
    Mail::fake();
    Notification::fake();

    $this->post('/metadata/677105/store/poe', [
        'audience_id' => 67,
        'meta_32' => 'España',
        'meta_4001' => 'Sí',
        'meta_4002' => 'No, aún no cuento con número EORI.',
    ])->assertRedirect();

    $answers = DB::table('customer_metas')->where('customer_id', 677105)->pluck('value', 'meta_data_id');

    expect($answers[4001])->toBe('Sí');
    expect($answers[4002])->toBe('No, aún no cuento con número EORI.');
    Mail::assertNothingSent();
    Notification::assertNothingSent();
});

it('AC9: stores only the residence answer when the customer is not a resident', function () {
    $this->post('/metadata/677105/store/poe', [
        'audience_id' => 67,
        'meta_32' => 'Colombia',
        'meta_4001' => 'No',
    ])->assertRedirect();

    expect(DB::table('customer_metas')->where('meta_data_id', 4001)->value('value'))->toBe('No');
    expect(DB::table('customer_metas')->where('meta_data_id', 4002)->exists())->toBeFalse();
});

it('AC10/AC11: shows the answers in the CRM POA accordion and keeps old submissions intact', function () {
    DB::table('customer_metas')->insert([
        ['customer_id' => 677105, 'meta_data_id' => 32, 'value' => 'Colombia', 'created_at' => '2025-01-10 10:00:00'],
        ['customer_id' => 677105, 'meta_data_id' => 32, 'value' => 'España', 'created_at' => '2026-10-29 10:00:00'],
        ['customer_id' => 677105, 'meta_data_id' => 4001, 'value' => 'Sí', 'created_at' => '2026-10-29 10:00:00'],
        ['customer_id' => 677105, 'meta_data_id' => 4002, 'value' => 'No, aún no cuento con número EORI.', 'created_at' => '2026-10-29 10:00:00'],
    ]);

    // Misma consulta que CustomerController@show usa para el acordeón POA
    $metas = CustomerMetaData::leftJoin('customer_metas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
        ->select('customer_meta_datas.value as name', 'customer_metas.value', 'customer_metas.created_at', 'customer_meta_datas.type_id', 'customer_meta_datas.parent_id')
        ->where('customer_id', '=', 677105)
        ->get();

    $html = view('customers.partials.acordion.poa', compact('metas'))->render();

    expect($html)->toContain('¿Es usted residente de la Unión Europea o Reino Unido?');
    expect($html)->toContain('No, aún no cuento con número EORI.');
    expect($html)->toContain('2025-01-10 10:00:00');
    expect($html)->toContain('Colombia');
    expect(substr_count($html, '¿Cuenta usted con un número EORI?'))->toBe(1);
});

it('shows the Maquiempanadas contact details in the POE footer', function () {
    $html = $this->get('/metadata/677105/create/poe/40')->assertOk()->getContent();

    expect($html)->toContain('3775 NW 46th STREET Miami / Florida 33142');
    expect($html)->toContain('Carrera 34 No 64-24 Manizales');
    expect($html)->toContain('href="https://wa.me/573148358924"');
    expect($html)->toContain('href="mailto:ventas@maquiempanadas.com"');
    expect($html)->toContain('Soporte técnico de lunes a jueves de 8 a 5 pm hora COL');
    expect($html)->toContain('¿Tienes preguntas?');
    expect($html)->toContain('href="https://www.instagram.com/maquiempanadas/"');
    expect($html)->toContain('href="https://www.youtube.com/@maquiempanadas"');
    expect($html)->toContain('href="https://www.facebook.com/maquiempanadasfans"');
    expect($html)->toContain('href="https://x.com/maquiempanadas"');
});
