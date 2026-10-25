---
slug: customer-instagram-fields
proposed_at: 2026-10-25
---

# Proposal — Plan técnico

## Resumen

Añadir `instagram_url` (VARCHAR 255 NULL) e `instagram_followers` (UNSIGNED BIGINT NULL) a `customers` mediante una migración idempotente con guardas `Schema::hasTable` / `Schema::hasColumn`, porque `instagram_url` ya fue creada a mano en producción. Exponer ambos campos en el modelo `Customer` (fillable + cast + helpers de normalización del handle) y mostrarlos en el bloque "Detalles" de la ficha, con el formulario de edición y `CustomerController::update()` actualizados para persistirlos.

## Cambios propuestos

- `database/migrations/2026_10_25_120000_add_instagram_fields_to_customers_table.php` — nueva. Añade las dos columnas a `customers` sólo si no existen.
- `app/Models/Customer.php` — añadir ambos campos a `$fillable`; añadir `$casts` con `instagram_followers => integer`; añadir `getInstagramHandle()` y `getInstagramUrl()` para normalizar el valor guardado a un enlace canónico.
- `resources/views/customers/show.blade.php` — bloque "Instagram" dentro de la tarjeta **Detalles**, entre Email y País (donde viven los campos de contacto). Enlace + badge de seguidores, condicional.
- `resources/views/customers/edit.blade.php` — dos inputs (`instagram_url`, `instagram_followers`) en el fieldset "Datos Personales", junto a Correo Electrónico.
- `app/Http/Controllers/CustomerController.php` — en `update()`, asignar ambos campos siguiendo el estilo de asignación explícita ya usado en ese método.
- `tests/Unit/CustomerInstagramFieldsTest.php` — nueva. Cobertura estática (sin BD) de migración, modelo y vistas.

### Convención descubierta en el repo (importante)

El repo **no usa asignación masiva para editar customers**. `CustomerController::update()` asigna propiedad por propiedad (`$model->email = $request->email; ...`), y varios campos del formulario (`contact_name`, `notes`, `rd_public_url`) ni siquiera están en `$fillable`. `$fillable` existe pero no es el mecanismo real de la edición por UI.

Consecuencia: añadir los campos a `$fillable` (pedido explícitamente y útil para importaciones/API/seeders) **no basta** para que el formulario los guarde. Por eso el cambio incluye también las asignaciones explícitas en `update()`. Se respeta el estilo del método en vez de refactorizarlo a `$model->fill(...)`, que sería un cambio de contrato fuera de alcance.

Segunda convención descubierta: el modelo `Customer` **no tenía `$casts`**. Se introduce la propiedad sólo con la entrada nueva; no se castea nada preexistente, para no alterar el comportamiento de otros campos.

Tercera: `linkedin_url` existe como columna y se usa en `index_partials/customer_header.blade.php`, pero no está en `$fillable` ni en el modelo — confirma que las columnas "sociales" del repo se consumen como atributos dinámicos de Eloquent. Los helpers nuevos (`getInstagramHandle`, `getInstagramUrl`) no son accessors de Eloquent (no terminan en `Attribute`), así que no hay colisión con el atributo `instagram_url`.

## Contratos afectados

Ninguno. No cambian endpoints, API Resources, eventos, jobs ni prompts de agentes IA.

Nota: `Customer::toSearchableArray()` devuelve `$this->toArray()`, así que los dos campos nuevos entrarán automáticamente en el array indexable. No hay driver de Scout activo que lo consuma hoy, pero queda registrado.

## Alternativas descartadas

- **Alternativa A — Guardar Instagram en `custom_fields` (columna `text` ya existente).** Descartada: `custom_fields` no es consultable con SQL de forma barata, impide ordenar/filtrar por seguidores más adelante, y el coordinador ya creó `instagram_url` como columna real en producción. Alinear el código con lo que ya existe en la base pesa más que la flexibilidad del campo libre.
- **Alternativa B — Migración no condicional + borrado manual previo de la columna en producción.** Descartada: exige una intervención manual en la base de 147k filas antes del deploy y deja una ventana en la que la columna no existe. La guarda `hasColumn` logra lo mismo sin tocar producción.
- **Alternativa C — Normalizar el handle al guardar (mutator) en vez de al leer.** Descartada por ahora: la columna ya tiene datos en producción cargados a mano, en formato desconocido. Normalizar sólo en lectura deja intacto el dato original y funciona igual para las filas ya cargadas. Un mutator se puede añadir después sin romper nada.
- **Alternativa D — `unsignedInteger` en vez de `unsignedBigInteger` para seguidores.** Descartada: `unsignedInteger` tope ~4.29e9 alcanzaría, pero el pedido especifica BIGINT y el costo de almacenamiento es irrelevante frente al riesgo de tener que modificar la columna después en una tabla grande.

## Plan de tests

El repo declara en `AGENTS.md`: *"This project has no local test database/environment available by default"*, y `.env.testing` apunta a un MySQL `ariwirechat_test` que no está levantado aquí. Además **no existe migración que cree `customers`** (sólo `2025_02_15_999999_document_customers_table.php`, documentation-only): la tabla es legacy de producción. Por eso un test `Feature` con `RefreshDatabase` no puede instanciar un `Customer`.

Se sigue la convención que ya usa el repo para estos casos (`tests/Unit/CustomerShowDetailsTest.php`, `CustomerIndexFilterSidebarTest.php`): **tests unitarios Pest de inspección estática** sobre el código fuente, sin BD.

- **Unit**: `tests/Unit/CustomerInstagramFieldsTest.php`
  - La migración existe, guarda con `Schema::hasTable` y con `hasColumn` para **ambas** columnas, declara `string('instagram_url', 255)->nullable()` y `unsignedBigInteger('instagram_followers')->nullable()`, y no crea índices → cubre AC1, AC2, AC3.
  - `Customer::$fillable` contiene ambos campos y `$casts` castea `instagram_followers` a `integer` → cubre AC4.
  - `getInstagramHandle()` / `getInstagramUrl()` normalizan handle suelto, `@handle`, URL con `www`, barra final y query string; devuelven `null` con valor vacío → cubre AC5 (ejercita el modelo sin tocar la BD).
  - `show.blade.php` contiene el bloque Instagram, usa `getInstagramUrl()`, es condicional y renderiza seguidores con `number_format` → cubre AC6, AC7.
  - `edit.blade.php` tiene ambos inputs y `CustomerController::update()` asigna ambos campos, con seguidores normalizado a `null` cuando viene vacío → cubre AC8.
- **Feature**: no aplica — ver justificación arriba.
- **Regresión / manual**: pendiente para el coordinador tras el deploy — abrir una ficha con Instagram cargado y confirmar enlace y seguidores; abrir una ficha sin Instagram y confirmar que no aparece el bloque.

## Impacto operativo

- **Migraciones**: reversible. `down()` elimina sólo las columnas presentes. **Advertencia**: un `migrate:rollback` borraría también `instagram_url` y los datos cargados a mano por el coordinador. El rollback debe ser una decisión explícita.
- **Feature flags**: no se usa. El cambio es aditivo y sin flag; el bloque de UI es condicional, así que un customer sin datos ve la ficha igual que hoy.
- **Datos existentes**: las ~147k filas quedan con ambas columnas en `NULL`. Los valores de `instagram_url` ya cargados en producción se conservan y se renderizan normalizados.
- **Tenants**: global, tenant único.
- **Documentación**: `2025_02_15_999999_document_customers_table.php` documenta el esquema de `customers` al 2025-02-15 y es documentation-only; no se edita (sería reescribir un snapshot histórico). Las columnas nuevas quedan documentadas en el docblock de la migración nueva. No aplica cambio de FAQ: no es funcionalidad de cara al público.

## Estimación

- **Complejidad**: baja.
- **Tiempo estimado LLM**: ~1 hora de trabajo agéntico.
- **Riesgo de regresión**: bajo. Áreas a vigilar: (1) `CustomerController::update()` — se tocan dos líneas en un método de ~50 asignaciones, (2) `show.blade.php` — bloque insertado dentro de la tarjeta "Detalles", verificar que no rompe el grid de Tailwind, (3) el deploy de la migración sobre la columna preexistente.

## Cambios respecto al plan original

Sin divergencias: la implementación siguió el plan. Las tres convenciones del repo descritas en **Cambios propuestos** se descubrieron durante el `/propose` (lectura del controlador y del modelo) y ya están incorporadas al plan, no son correcciones posteriores.
