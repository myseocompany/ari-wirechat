---
slug: poa-residencia-ue-eori
proposed_at: 2026-10-29
---

# Proposal — Plan técnico

## Resumen

Se agrega a la campaña 40 un grupo de metadata nuevo, "Residencia y número EORI", con dos preguntas tipo radio: residencia UE/Reino Unido y EORI. Se crea con una migración de datos que usa IDs fijos del bloque 4000. El formulario POE pinta ese grupo con una vista parcial dedicada que trae los avisos y un JS que muestra, oculta y habilita los campos según las respuestas. Las respuestas se guardan por el mismo camino de siempre (`meta_<id>` → `customer_metas`), y por eso aparecen solas en el acordeón POA del CRM, sin tocar el controlador de guardado.

## Contexto técnico verificado (29/10/2026)

- **Lectura del formulario:** `GET metadata/{id}/create/poe/{cid}` → `MetaDataController::createPoe` → `resources/views/POE/create.blade.php`. La vista recorre `$campaign->customerMetaData`, los grupos asociados a la campaña en `campaign_customer_meta_data`, y dentro de cada grupo sus hijos, ordenados por `weight`.
- **Pivote sin orden:** `campaign_customer_meta_data` (`id, campaign_id, customer_meta_data_id, timestamps`) no tiene columna de orden. Hoy los grupos 14, 20, 28 y 34 salen en el orden implícito de MySQL y tienen `weight = NULL`.
- **Radios agrupados:** el render genérico de los grupos (`type_id NULL`, que `isMultiple()` trata como `true`) le da a cada hijo su propio `name="meta_<hijo>"` y lo marca `required`. Así un radio Sí/No no queda agrupado, y un campo oculto bloquearía el envío. Por eso hace falta un render dedicado.
- **Guardado:** `POST metadata/{id}/store/poe` → `storepoe` guarda cada `meta_<id>` no nulo como una fila de `customer_metas` (`meta_data_id`, `value`, `customer_id`, `audience_id`). Los inputs `disabled` no se envían.
- **Acordeón POA:** `CustomerController` (≈ línea 1066) une `customer_meta_datas` con `customer_metas` del cliente, y `acordion/poa.blade.php` muestra `name`/`value` agrupados por `created_at`, excluyendo `parent_id` 1 y 8. Cualquier hijo nuevo de un grupo distinto aparece automáticamente.
- **Tablas fuera de las migraciones:** `customer_meta_datas` y `campaign_customer_meta_data` no tienen migración en el repo (solo existen en producción y en el dump `database/mqe_2025_12_10.sql`).
- **IDs:** el bloque 4000–4099 está libre en producción. La convención existente es reservar bloques de IDs fijos (2001–2007 para la campaña 44, 3000 para la 45). El ID máximo actual es 30019.
- **FAQs:** `resources/views/public/faq.blade.php` y `docs/FAQ_ARICRM.md` no existen en el repo.

## Cambios propuestos

- `database/migrations/<timestamp>_add_poe_residencia_ue_eori_metadata.php` — **migración de datos**, idempotente y protegida con `Schema::hasTable`:
  - Inserta en `customer_meta_datas` el grupo `4000` "Residencia y número EORI" y sus preguntas:
    - `4001` "¿Es usted residente de la Unión Europea o Reino Unido?" (type 2, weight 1).
    - `4002` "¿Cuenta usted con un número EORI?" (type 2, weight 2).
  - Las opciones son hijos de cada pregunta y son las que se guardan como valor:
    - `4003` "Sí" y `4004` "No" (parent 4001).
    - `4005` "Sí, cuento con número EORI." y `4006` "No, aún no cuento con número EORI." (parent 4002).
  - Si existe la campaña 40, la asocia al grupo 4000 en `campaign_customer_meta_data`.
  - Asigna `weight` a los grupos: 14→10, 20→20, 28→30, 4000→35, 34→40.
  - `down()`: quita la asociación pivote, borra las filas 4000–4006 y devuelve los `weight` de los grupos a `NULL`. **No borra** `customer_metas` (las respuestas de clientes se conservan).
- `app/Http/Controllers/MetaDataController.php` (`createPoe`) — cargar los grupos de la campaña ordenados por `weight` y luego por `id`, para que el grupo nuevo quede entre "Información de destino" y "Veracidad". El cambio se limita a esta acción; la relación `Campaign::CustomerMetaData` no se toca, para no alterar las campañas 38, 44 y 45.
- `resources/views/POE/create.blade.php` — dentro del bucle de grupos, si el grupo es el 4000, incluir la vista parcial dedicada en lugar del render genérico. Añadir al bloque `<script>` existente la lógica:
  - Pregunta de residencia obligatoria.
  - Con residencia = Sí: mostrar el aviso y la pregunta EORI (obligatoria y habilitada).
  - Con EORI = No: mostrar el segundo aviso.
  - Con residencia = No: ocultar todo y **deshabilitar** los radios EORI, para que no se envíen (AC6).
- `resources/views/POE/partials/residencia_eori.blade.php` (**nuevo**) — radios con `name="meta_4001"` / `name="meta_4002"` y `<label>` asociados, más los dos avisos con el texto literal aprobado ("…al residir en la Unión Europea o el Reino Unido…"). El segundo aviso va en estilo de advertencia.
- `tests/Feature/PoeResidenciaEoriTest.php` (**nuevo**) — escenarios del plan de tests. Crea las tablas legacy en el `setUp` si no existen.
- `resources/views/POE/create3.blade.php` — **no se toca**: ninguna ruta la usa (`createPoe` renderiza `POE.create`).

## Contratos afectados

- **Endpoint `GET metadata/{id}/create/poe/{cid}`:** el HTML cambia (bloque nuevo y orden de grupos explícito). La firma y los parámetros no cambian.
- **Endpoint `POST metadata/{id}/store/poe`:** sin cambio de contrato ni de código. Recibe dos campos `meta_*` más, que ya acepta de forma genérica.
- **Datos:** filas nuevas en `customer_meta_datas` (4000–4006) y en `campaign_customer_meta_data`, más `weight` en 4 grupos existentes.
- **Eventos, jobs, API Resources y prompts de agentes IA:** no aplica.

## Alternativas descartadas

- **A. Campos HTML fijos (`eu_resident`, `eori`) y guardado específico en `storepoe`.** Descartada porque obliga a añadir lógica nueva en un endpoint público con vulnerabilidades abiertas (`fix/poe-endpoint-hardening`). Además, el acordeón POA no los mostraría sin filas en `customer_meta_datas`.
- **B. Motor genérico de preguntas condicionales** (columnas `show_if` en `customer_meta_datas` y render dinámico). Descartada: cambia el esquema de una tabla legacy sin migraciones y está fuera de alcance según el refined. Es sobreingeniería para un solo caso.
- **C. Crear las filas a mano en producción vía tinker.** Descartada: no se puede reproducir en tests ni en local, no deja rastro en el repo y no tiene rollback.
- **D. Ordenar con `orderBy('weight')` en la relación `Campaign::CustomerMetaData`.** Descartada: afecta a todas las campañas que usan esa relación; se ordena solo en `createPoe`.
- **E. Insertar el bloque nuevo justo antes del grupo 34 comprobando su ID en la vista.** Descartada: añade otro ID fijo al Blade. El `weight` deja el orden como dato configurable.

## Plan de tests

Precondición de `/verify`: hoy la suite no corre en local. Falta la base de datos `ariwirechat_test`, y Pest falla por un `uses()` duplicado (`tests/Feature/CustomerActionTypeOrderTest.php`). En `/apply` se crea la base de prueba y se corre el test con filtro. El arreglo de Pest se reporta en el testing report y, si es trivial, va como fastpath aparte.

- **Feature**: `tests/Feature/PoeResidenciaEoriTest.php`
  - Escenarios GET:
    - Muestra la pregunta de residencia con exactamente dos radios del mismo `name` y `required` — **AC1**.
    - Muestra el aviso literal con "Unión Europea o el Reino Unido" y la pregunta EORI con sus dos opciones literales — **AC3**.
    - El segundo aviso existe en el DOM — **AC4**.
    - Orden de bloques: destino < residencia/EORI < veracidad.
    - Los radios EORI se renderizan `disabled` de entrada — base de **AC2/AC6**.
  - Escenarios POST:
    - Con residencia = Sí y EORI = "No, aún no…": se aceptan y guardan las 2 respuestas — **AC7, AC8**.
    - Con residencia = No y sin EORI: se guarda 1 respuesta y ninguna EORI — **AC9**.
    - No se envían notificaciones (`Mail::fake`/`Notification::fake` sin envíos) — **AC7**.
  - Escenarios del acordeón POA:
    - Un usuario autenticado ve la pregunta y la respuesta en la ficha del cliente — **AC10**.
    - Un cliente con un POE antiguo, sin estas respuestas, se ve igual, sin filas vacías — **AC11**.
  - Otra campaña que use el formulario no muestra el bloque (multi-tenancy).
- **Migración**: `up` es idempotente (se puede correr dos veces sin duplicar), y `down` elimina las filas 4000–4006 y la asociación sin borrar `customer_metas`.
- **Regresión / manual (navegador móvil y de escritorio)**: la lógica JS de **AC2–AC6** se valida con un checklist en un navegador real (Chrome y Safari móvil), porque el repo no tiene Dusk ni Playwright. Se cubren:
  - Residencia No.
  - Residencia Sí con EORI Sí.
  - Residencia Sí con EORI No (se ve el aviso dos veces).
  - Cambio de Sí a No (desaparece todo y se envía sin EORI).
  - Envío con "Individual" (el grupo 14 sigue oculto y el bloque EORI sí aparece).

## Impacto operativo

- **Migraciones**: una migración de datos, reversible con `down()`. Al hacer deploy la ejecuta Forge con `php artisan migrate`. **Ejecutarla en producción requiere aprobación humana explícita** (POLICIES). Rollback: `php artisan migrate:rollback --step=1` y revert del commit de la vista. Las respuestas ya guardadas quedan en `customer_metas` y dejan de mostrarse en el POA mientras no existan sus filas de metadata.
- **Feature flags**: no. El bloque solo existe si la campaña tiene asociado el grupo 4000, así que desasociar el pivote apaga la feature sin hacer deploy.
- **Datos existentes**: no se modifican respuestas. Solo cambian los `weight` de los grupos 14, 20, 28 y 34, que hoy son `NULL` y solo los usa la campaña 40.
- **Tenants**: solo MQE (instancia arichat.co, campaña 40). Las campañas 38, 44 y 45 no se ven afectadas.
- **Documentación**: los archivos de FAQ (`resources/views/public/faq.blade.php` y `docs/FAQ_ARICRM.md`) **no existen en el repo**, así que no hay que actualizar FAQ. Se actualizan `04-testing-report.md` y `graphify update .`.
- **Comunicación**: confirmar con la administración de MQE una vez desplegado, y enviarles un enlace de prueba con un cliente ficticio (sin datos reales).

## Estimación

- **Complejidad**: baja–media.
- **Tiempo estimado LLM**: 2–3 h, incluida la preparación del entorno de tests local.
- **Riesgo de regresión**: **medio** en el formulario POE. Por el orden de los grupos y por los `required` / `disabled`, si algo sale mal se pueden bloquear envíos que alimentan el despacho aduanero. Riesgo bajo en el acordeón POA (solo agrega filas).

## Cambios respecto al plan original

*(Esta sección se rellena durante `/apply` si la realidad diverge del plan. No borrar el plan original — anexar lo que cambió y por qué.)*

Durante `/apply` (29/10/2026):

- **Test en `tests/Unit/` en vez de `tests/Feature/`**: `tests/Pest.php` aplica `RefreshDatabase` a `Feature`, que exige MySQL (`ariwirechat_test` no existe), y la suite Feature no carga por el `uses()` duplicado. Se siguió el precedente del repo (`customer-instagram-fields`, `CustomerFileUploadTest`): test en `Unit` con `TestCase` y SQLite en memoria, sin tocar ninguna base real. No hizo falta crear la base de prueba ni arreglar Pest en este feature.
- **Constantes en `app/Models/CustomerMetaData.php`**: los IDs 4000/4001/4002 y los valores "Sí" / "No, aún no cuento con número EORI." quedan como constantes del modelo para que la vista y el JS no repitan números ni textos sueltos. La migración mantiene sus IDs propios (no depende del modelo).
- **Segundo parcial `resources/views/POE/partials/eori_notice.blade.php`**: el texto del aviso se escribe una sola vez y se incluye dos veces (aviso + recordatorio), así no pueden divergir.
- **JS dentro del parcial** y no en el bloque `<script>` de `create.blade.php`: la lógica solo existe si el grupo está asociado a la campaña.

- **Footer de contacto en el formulario POE** (pedido del solicitante durante `/verify`, 29/10/2026): nuevo parcial `resources/views/POE/partials/footer.blade.php` con "¿Tienes preguntas?" (teléfonos y redes: Instagram, YouTube, Facebook, X) y las direcciones/teléfonos de USA y Colombia, email de ventas y soporte técnico. Solo se incluye en `POE/create.blade.php`; el layout `api` no se toca. URLs de redes tomadas del repo y verificadas con HTTP 200 el 29/10/2026.
