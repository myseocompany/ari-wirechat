<?php

use App\Models\CustomerMetaData;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

uses(TestCase::class);

function hardeningMigration(string $file): object
{
    return require database_path('migrations/'.$file);
}

function hardeningStoreUrl(int $customerId = 677105, int $campaignId = 40): string
{
    return URL::signedRoute('poe.store', ['id' => $customerId, 'cid' => $campaignId]);
}

function hardeningCreateUrl(int $customerId = 677105, int $campaignId = 40): string
{
    return URL::signedRoute('poe.create', ['id' => $customerId, 'cid' => $campaignId]);
}

/** Envío completo y válido (tipo Sociedad, residente UE con EORI). */
function hardeningPayload(array $override = []): array
{
    return array_merge([
        'meta_4007' => 'Sociedad',
        'meta_15' => 'Mi Empresa SAS',
        'meta_22' => 'Persona de prueba',
        'meta_32' => 'España',
        'meta_27' => 'on',
        'meta_4001' => 'Sí',
        'meta_4002' => 'Sí, cuento con número EORI.',
    ], $override);
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
        $table->unsignedBigInteger('id');
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
    DB::table('campaigns')->insert(['id' => 40, 'name' => 'POE']);
    DB::table('campaigns')->insert(['id' => 41, 'name' => 'Otra']);

    $rows = [
        [14, null, 'Información de la compañía', null],
        [15, 14, 'Nombre completo de la compañía', 1],
        [20, null, 'Información de contacto', null],
        [22, 20, 'Nombre completo', 1],
        [28, null, 'Información de destino', null],
        [32, 28, 'País', 1],
        [34, null, 'Veracidad de la información', null],
        [27, 34, 'Declaro que la información que se suministra es veraz', 3],
        [99, null, 'Campo ajeno a la campaña', null],
        [98, 99, 'Pregunta de otra campaña', 1],
    ];
    foreach ($rows as [$id, $parentId, $value, $typeId]) {
        DB::table('customer_meta_datas')->insert(['id' => $id, 'parent_id' => $parentId, 'value' => $value, 'type_id' => $typeId]);
    }
    foreach ([34, 28, 20, 14] as $groupId) {
        DB::table('campaign_customer_meta_data')->insert(['id' => $groupId, 'campaign_id' => 40, 'customer_meta_data_id' => $groupId]);
    }

    // Respuestas históricas: tipo de importación guardado bajo el grupo 34 y una veracidad real
    DB::table('customer_metas')->insert([
        ['customer_id' => 1, 'meta_data_id' => 34, 'audience_id' => 67, 'value' => 'Individual'],
        ['customer_id' => 2, 'meta_data_id' => 34, 'audience_id' => 67, 'value' => 'on'],
    ]);

    hardeningMigration('2026_10_29_120000_add_poe_residencia_ue_eori_metadata.php')->up();
    hardeningMigration('2026_11_03_100000_split_poe_import_type_metadata.php')->up();

    DB::table('customer_metas')->whereIn('customer_id', [1, 2])->get(); // línea base
    Carbon::setTestNow('2026-11-05 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

it('AC12: happy path saves the answers, fixes the audience and redirects', function () {
    $this->post(hardeningStoreUrl(), hardeningPayload())
        ->assertRedirect('http://maquiempanadas.com/');

    $saved = DB::table('customer_metas')->where('customer_id', 677105)->pluck('value', 'meta_data_id')->all();

    expect($saved)->toEqualCanonicalizing([
        4007 => 'Sociedad', 15 => 'Mi Empresa SAS', 22 => 'Persona de prueba', 32 => 'España',
        27 => 'on', 4001 => 'Sí', 4002 => 'Sí, cuento con número EORI.',
    ]);
    expect(DB::table('customer_metas')->where('customer_id', 677105)->pluck('audience_id')->unique()->all())->toBe([67]);
});

it('AC12: legacy forms still posting meta_34 keep working', function () {
    $payload = hardeningPayload();
    $payload['meta_34'] = $payload['meta_4007'];
    unset($payload['meta_4007']);

    $this->post(hardeningStoreUrl(), $payload)->assertRedirect('http://maquiempanadas.com/');

    expect(DB::table('customer_metas')->where('customer_id', 677105)->where('meta_data_id', 4007)->value('value'))->toBe('Sociedad');
    expect(DB::table('customer_metas')->where('customer_id', 677105)->where('meta_data_id', 34)->count())->toBe(0);
});

it('AC1/AC2/AC9: ignores files, project_id and login fields', function () {
    $payload = hardeningPayload([
        'project_id' => '../../../etc',
        'login_1' => ['a', 'b', 'c', 'secreto'],
        'meta_55' => UploadedFile::fake()->create('shell.php', 1),
        'otro' => UploadedFile::fake()->create('x.php', 1),
    ]);

    $this->post(hardeningStoreUrl(), $payload)->assertRedirect('http://maquiempanadas.com/');

    expect(is_dir(base_path('public/files')))->toBeFalse();
    expect(DB::table('customer_metas')->where('meta_data_id', 55)->count())->toBe(0);
    expect(DB::table('customer_metas')->where('customer_id', 677105)->count())->toBe(7);
});

it('AC3/AC4: saves only campaign fields and takes the audience from the server', function () {
    $this->post(hardeningStoreUrl(), hardeningPayload(['meta_98' => 'ajeno', 'meta_1' => 'x', 'radio_15' => 'y', 'check_15' => 'z', 'audience_id' => 1]))
        ->assertRedirect();

    $ids = DB::table('customer_metas')->where('customer_id', 677105)->pluck('meta_data_id')->all();
    expect($ids)->not->toContain(98)->not->toContain(1);
    expect(count($ids))->toBe(7);
    expect(DB::table('customer_metas')->where('customer_id', 677105)->where('audience_id', '!=', 67)->count())->toBe(0);
});

it('AC5: a missing customer returns 404 and saves nothing', function () {
    $this->get(hardeningCreateUrl(999999))->assertNotFound();
    $this->post(hardeningStoreUrl(999999), hardeningPayload())->assertNotFound();

    expect(DB::table('customer_metas')->where('customer_id', 999999)->count())->toBe(0);
});

it('AC6: signed links work and tampered or unsigned links expire after the grace period', function () {
    $this->get(hardeningCreateUrl())->assertOk()->assertSee('Cliente de prueba');

    // Id alterado: nunca entra, ni en periodo de gracia, y no revela el nombre
    $tampered = str_replace('/metadata/677105/', '/metadata/1/', hardeningCreateUrl());
    DB::table('customers')->insert(['id' => 1, 'name' => 'Otro cliente']);
    $this->get($tampered)->assertForbidden()->assertDontSee('Otro cliente');

    // Sin firma, dentro de la gracia: funciona
    $this->get('/metadata/677105/create/poe/40')->assertOk();

    // Sin firma, después de la gracia: rechazado; con firma sigue funcionando
    Carbon::setTestNow('2026-12-11 08:00:00');
    $this->get('/metadata/677105/create/poe/40')->assertForbidden()->assertDontSee('Cliente de prueba');
    $this->get(hardeningCreateUrl())->assertOk();
    $this->post('/metadata/677105/store/poe', hardeningPayload())->assertForbidden();
});

it('AC7: rejects incomplete or invalid submissions, keeps the input and saves nothing', function () {
    $cases = [
        'falta contacto' => ['meta_22' => ''],
        'falta destino' => ['meta_32' => null],
        'falta veracidad' => ['meta_27' => ''],
        'falta residencia' => ['meta_4001' => ''],
        'falta tipo' => ['meta_4007' => ''],
        'falta empresa (Sociedad)' => ['meta_15' => ''],
        'EORI obligatorio si es residente' => ['meta_4002' => ''],
        'radio fuera de opciones' => ['meta_4001' => 'Quizás'],
        'tipo fuera de catálogo' => ['meta_4007' => 'Pirata'],
        'array en campo escalar' => ['meta_22' => ['a', 'b']],
        'texto demasiado largo' => ['meta_22' => str_repeat('a', 2001)],
    ];

    $i = 0;
    foreach ($cases as $label => $override) {
        // IP distinta por caso: aquí no se prueba el límite de envíos
        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.'.++$i])
            ->post(hardeningStoreUrl(), hardeningPayload($override));
        $response->assertRedirect()->assertSessionHasErrors();
        expect(DB::table('customer_metas')->where('customer_id', 677105)->count())->toBe(0, $label);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.1'])->post(hardeningStoreUrl(), hardeningPayload(['meta_22' => '']));
    $html = $this->get(hardeningCreateUrl())->getContent();
    expect($html)->toContain('Falta responder: Nombre completo.');
    expect($html)->toContain('value="Mi Empresa SAS"');
});

it('AC7: company data is optional for Individual and EORI is ignored for non-residents', function () {
    $this->post(hardeningStoreUrl(), hardeningPayload(['meta_4007' => 'Individual', 'meta_15' => '']))->assertRedirect('http://maquiempanadas.com/');
    $this->post(hardeningStoreUrl(), hardeningPayload(['meta_4001' => 'No', 'meta_4002' => 'Sí, cuento con número EORI.']))->assertRedirect('http://maquiempanadas.com/');

    $second = DB::table('customer_metas')->where('customer_id', 677105)->where('meta_data_id', 4002)->count();
    expect($second)->toBe(1); // solo el primer envío (residente) guardó EORI
});

it('AC8: the sixth submission within a minute gets 429 and saves nothing', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(hardeningStoreUrl(), hardeningPayload(['meta_22' => '']))->assertRedirect();
    }

    $this->post(hardeningStoreUrl(), hardeningPayload())->assertStatus(429);
    expect(DB::table('customer_metas')->where('customer_id', 677105)->count())->toBe(0);
});

it('AC10: a failure while saving leaves no partial answers', function () {
    DB::unprepared("CREATE TRIGGER boom BEFORE INSERT ON customer_metas WHEN NEW.meta_data_id = 32 BEGIN SELECT RAISE(ABORT, 'boom'); END");

    $this->withoutExceptionHandling();
    expect(fn () => $this->post(hardeningStoreUrl(), hardeningPayload()))->toThrow(Exception::class);

    expect(DB::table('customer_metas')->where('customer_id', 677105)->count())->toBe(0);
});

it('AC11: migration reassigns historical import types, is idempotent and reversible', function () {
    $migration = hardeningMigration('2026_11_03_100000_split_poe_import_type_metadata.php');

    expect(DB::table('customer_meta_datas')->where('id', 4007)->value('value'))->toBe('Importación de manera');
    expect(DB::table('customer_metas')->where('customer_id', 1)->value('meta_data_id'))->toBe(4007);
    expect(DB::table('customer_metas')->where('customer_id', 2)->value('meta_data_id'))->toBe(34); // veracidad intacta

    $label = CustomerMetaData::leftJoin('customer_metas', 'customer_meta_datas.id', 'customer_metas.meta_data_id')
        ->where('customer_id', 1)->value('customer_meta_datas.value');
    expect($label)->toBe('Importación de manera');

    $migration->up();
    expect(DB::table('customer_meta_datas')->where('id', 4007)->count())->toBe(1);

    $migration->down();
    expect(DB::table('customer_meta_datas')->where('id', 4007)->count())->toBe(0);
    expect(DB::table('customer_metas')->where('customer_id', 1)->value('meta_data_id'))->toBe(34);
    expect(DB::table('customer_metas')->count())->toBe(2);
});

it('AC13: rejections are logged without submitted values', function () {
    Log::spy();

    $this->post(hardeningStoreUrl(), hardeningPayload(['meta_22' => 'VALOR-SECRETO-123', 'meta_4001' => '']))->assertRedirect();
    $this->get(hardeningCreateUrl(999999))->assertNotFound();

    Log::shouldHaveReceived('warning')->withArgs(function ($message, $context = []) {
        return $message === 'poe.rejected'
            && ($context['reason'] ?? null) === 'validation'
            && $context['customer_id'] === 677105
            && $context['campaign_id'] === 40
            && ! str_contains(json_encode($context), 'VALOR-SECRETO-123');
    })->once();
    Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context = []) => ($context['reason'] ?? null) === 'not_found')->once();
});

it('renders the form with its signed action, without audience or campaign hidden fields', function () {
    $html = $this->get(hardeningCreateUrl())->assertOk()->getContent();

    expect($html)->toContain('name="meta_4007"');
    expect($html)->not->toContain('name="meta_34"');
    expect($html)->not->toContain('name="audience_id"');
    expect($html)->toContain('/metadata/677105/store/poe?cid=40');
    expect($html)->toContain('signature=');
});
