---
slug: poe-endpoint-hardening
proposed_at: 2026-11-01
---

# Proposal — Plan técnico

Clasificación: **retención**. Los 10 supuestos `[CONFIRMAR]` de `02-refined.md` fueron confirmados por el usuario el 2026-11-01 (30 días de transición, enlace firmado sin expiración, nombre visible con enlace válido, 5 envíos/min/IP, sin captcha, empresa obligatoria si no es "Individual", campo propio "Importación de manera" + reasignación histórica, volver al formulario con datos conservados, huérfanos intactos, sin subida de archivos).

## Resumen

El POST del POE deja de recorrer `$request->all()` a ciegas. Un servicio nuevo deriva de la campaña la lista de campos válidos y las reglas de validación, y guarda todo en una transacción con `audience_id` fijado por el servidor. Se eliminan la subida de archivos y el bloque `login_*`. Los enlaces pasan a ser firmados (con 30 días de gracia para los ya enviados), el POST lleva límite de 5/min por IP y el tipo de importación pasa a tener su propio campo (id 4007) con migración de datos reversible.

## Cambios propuestos

**Rutas y middleware**
- `routes/web.php` (líneas 530-531) — dar nombre a las rutas (`poe.create`, `poe.store`); el POST añade `throttle:poe-submit`; ambas pasan por el middleware de enlace POE.
- `app/Http/Middleware/EnsurePoeLink.php` (nuevo) — acepta enlace con firma válida; acepta enlace sin firma solo hasta `config('poe.legacy_links_until')` y lo registra en el log; después responde 403. No revela el nombre del cliente en ningún rechazo.
- `app/Providers/AppServiceProvider.php` — define el limiter `poe-submit` (5/min por `request()->ip()`); su respuesta 429 registra el rechazo (AC13).
- `config/poe.php` (nuevo) — `audience_id` (67), `legacy_links_until` (fecha de deploy + 30 días), `max_value_length`, `submissions_per_minute`.

**Controlador y lógica**
- `app/Http/Controllers/MetaDataController.php` — `createPoe` usa `findOrFail` (404 si no existe) y le pasa a la vista un `action` firmado para el POST; `storepoe` delega en el servicio, captura `ValidationException` (vuelve al formulario con datos conservados y errores) y mantiene la redirección final. Se borran de `storepoe` los bloques `radio_`, `check_`, `login_` y el de archivos. `saveCustomerMeta` (usada también por otros formularios) **no se toca**.
- `app/Services/Poe/PoeSubmissionService.php` (nuevo) — a partir de la campaña: (1) calcula los ids permitidos (hijos de sus grupos + 4007); (2) construye las reglas (todo obligatorio, salvo los hijos del grupo 14 cuando 4007 = "Individual"; 4002 obligatorio solo si 4001 = "Sí"; radios y el select limitados a sus opciones; escalares con longitud máxima); (3) ignora cualquier otro campo, incluidos `project_id`, `audience_id` y archivos; (4) inserta en `customer_metas` dentro de `DB::transaction` con `audience_id` de config; (5) escribe el log de rechazos sin valores.

**Modelo**
- `app/Models/CustomerMetaData.php` — constantes `POE_IMPORT_TYPE_ID = 4007`, `POE_INDIVIDUAL = 'Individual'`.

**Vistas**
- `resources/views/POE/create.blade.php` — `name="meta_34"` pasa a `meta_4007`; el `action` usa la URL firmada; se quita el hidden `audience_id`; se muestra el bloque de errores al inicio y se repuebla con `old()`; el JS que lee `#category` queda igual (se apoya en el id, no en el name).
- `resources/views/POE/partials/residencia_eori.blade.php` — repoblar los radios con `old()`.
- `resources/views/POE/create3.blade.php` — sin cambios de comportamiento (no tiene ruta); solo se ajusta su `name` para no reintroducir la colisión.
- `resources/views/customers/show.blade.php:102` — el botón "POA" copia la URL firmada (`URL::signedRoute('poe.create', …)`).

**Datos**
- `database/migrations/2026_11_02_100000_split_poe_import_type_metadata.php` (nuevo) — crea la fila 4007 "Importación de manera" (mismo `parent_id` que el grupo 34, `type_id` 2, guardada con guarda `Schema::hasTable`, idempotente); la vincula a la campaña 40 solo si hace falta (id del pivote explícito, `id` no es AUTO_INCREMENT en producción); reasigna `customer_metas` con `meta_data_id = 34` y valor en el catálogo de seis tipos (`Individual`, `Sociedad`, …) a `meta_data_id = 4007`. Guarda en una tabla de respaldo los ids reasignados para el `down()`.

**Tests**
- `tests/Unit/PoeEndpointHardeningTest.php` (nuevo, sqlite en memoria, mismo patrón que `PoeResidenciaEoriTest`) y ajustes a `tests/Unit/PoeResidenciaEoriTest.php` si el cambio de `name` lo requiere.

**Docs**
- `docs/` y FAQ: este cambio no añade capacidad visible nueva para usuarios/admins; no aplica `faq.blade.php` / `FAQ_ARICRM.md`. Se agrega una nota operativa en `TASKS.md` (aviso a asesores: copiar el enlace nuevo con "POA").

## Contratos afectados

- `GET metadata/{id}/create/poe/{cid}`: abierto con cualquier id → exige firma válida, o enlace sin firma solo hasta `legacy_links_until`; cliente inexistente → 404 (antes: error 500 al enviar, o formulario vacío).
- `POST metadata/{id}/store/poe`: acepta solo `meta_<id>` de la campaña, firma en la URL, 5/min/IP (429), errores de validación con retorno al formulario; ya no acepta archivos, `project_id`, `audience_id`, `radio_*`, `check_*`, `login_*`.
- Campo del select de importación: `meta_34` → `meta_4007`. El `POST` de formularios con la página abierta antes del deploy (con `meta_34`) debe seguir funcionando: el servicio acepta `meta_34` como alias de 4007 durante la ventana de transición para no perder esos envíos.
- Datos: `customer_metas.meta_data_id` 34 → 4007 para los valores de tipo de importación (reversible). El POA de clientes anteriores deja de mostrar "Veracidad de la información = Individual".
- Ningún otro formulario de `metadata/*`, ningún job ni evento.

## Alternativas descartadas

- **`FormRequest` estático con reglas fijas**: descartada; las preguntas viven en BD y cambian por campaña (el EORI se añadió por migración). Reglas fijas se desincronizarían de la vista; derivarlas de la campaña mantiene una sola fuente.
- **Captcha (Turnstile) en lugar de, o además de, firma + límite**: descartada por decisión del usuario; añade fricción a clientes que ya pagaron y una dependencia externa. Queda abierta si el log de rechazos muestra abuso.
- **Firmar con expiración (`temporarySignedRoute`)**: descartada por decisión del usuario; un cliente puede abrir el enlace semanas después.
- **Quitar la firma y usar un token opaco por cliente en BD**: descartada; requiere columna/migración y backfill de ~680 mil clientes frente a `URL::signedRoute`, que no necesita almacenamiento.
- **Corregir solo la etiqueta del grupo 34 (renombrar la fila)**: descartada; el grupo 34 también agrupa la declaración de veracidad, y renombrarlo rompe esa etiqueta. Un campo propio separa ambos significados.
- **Mantener la subida de archivos pero validada**: descartada; la campaña 40 no tiene campo de archivo. Deshabilitar es más seguro que validar; si hace falta, otro spec.
- **Restringir PHP a `index.php` en nginx**: complementaria, no sustituta; es infraestructura y queda como tarea aparte.

## Plan de tests

Todos en `tests/Unit/PoeEndpointHardeningTest.php` con sqlite en memoria (el entorno de Feature no carga por problemas preexistentes; se sigue el precedente de `PoeResidenciaEoriTest`). Las llamadas HTTP se hacen con `$this->get/post` sobre las rutas reales y tablas creadas en `beforeEach`.

- AC1 — POST con archivos `.php` en `meta_55` y en `otro`: respuesta correcta, ningún archivo en disco, ningún `ProjectDocument`.
- AC2 — `project_id=../..` no genera ruta ni registro.
- AC3 — campos de otra campaña/otro id no se guardan; los válidos sí.
- AC4 — `audience_id=1` en el request → se guarda 67.
- AC5 — cliente inexistente: GET y POST → 404, cero filas.
- AC6 — URL firmada correcta funciona; id modificado → 403 sin nombre en el cuerpo; sin firma antes de `legacy_links_until` funciona y deja log; sin firma después → 403 (`Carbon::setTestNow`).
- AC7 — falta cada categoría obligatoria; empresa solo obligatoria si no es "Individual"; EORI obligatorio si residencia = Sí; valor de radio inválido; array en campo escalar → retorno con errores, datos conservados, cero filas.
- AC8 — el sexto envío en un minuto → 429, nada guardado.
- AC9 — POST con `login_*` → sin error y sin registro.
- AC10 — fallo forzado a mitad del guardado (excepción en el tercer insert) → cero filas.
- AC11 — tras la migración, el POA del cliente muestra "Importación de manera"; test de migración: reasigna, es idempotente y `down()` restaura.
- AC12 — camino feliz completo (incluye 4001/4002) guarda las mismas respuestas que antes salvo 34→4007 y redirige a maquiempanadas.com; alias `meta_34` aceptado.
- AC13 — cada rechazo deja una entrada de log con motivo, IP, `customer_id`, campaña, y ningún valor enviado.
- Regresión manual en producción tras el deploy (con aprobación del usuario): abrir enlace firmado y enlace viejo con el cliente de prueba 678620 y enviar el formulario completo; revisar el POA.

## Impacto operativo

- **Migraciones**: una migración de datos, reversible. Rollback: `down()` devuelve a `meta_data_id = 34` las filas registradas en la tabla de respaldo y elimina la fila 4007 y su vínculo con la campaña; no borra respuestas. Requiere aprobación explícita del usuario para ejecutarse en producción (Forge la corre en el deploy; se avisa antes de mergear a `main`).
- **Feature flags**: no hay flag; el interruptor de seguridad es `legacy_links_until` en `config/poe.php` (variable de entorno opcional) para ampliar o recortar la gracia sin tocar código.
- **Datos existentes**: 256 huérfanos intactos (decisión confirmada). Las respuestas históricas del grupo 34 se reasignan a 4007.
- **Tenants**: solo MQE (campaña 40, audiencia 67).
- **Coordinación**: avisar a los asesores de MQE, a partir del deploy, de copiar el enlace con el botón "POA". Los enlaces viejos siguen valiendo 30 días.
- **IP real**: Cloudflare está delante y nginx aplica `set_real_ip_from` (`/etc/nginx/conf.d/cloudflare.conf`), así que `request()->ip()` ve la IP del cliente; se confirma con un envío de prueba y el log.
- **Observabilidad**: el log de rechazos permite medir falsos positivos durante la primera semana; revisar antes de dar por cerrada la transición.
- **Documentación**: nota en `TASKS.md`; no aplica FAQ pública.

## Estimación

- **Complejidad**: media.
- **Tiempo estimado LLM**: ~2-3 horas de trabajo agéntico (servicio + middleware + migración + 13 grupos de tests + verificación).
- **Riesgo de regresión**: medio. Áreas: (1) validación demasiado estricta sobre el camino feliz de ~35 envíos/mes; (2) enlaces viejos y firma (mitigado con la gracia y la prueba en producción); (3) la reasignación 34→4007 sobre datos históricos (mitigada con respaldo y `down()`); (4) formularios abiertos durante el deploy (alias `meta_34`).

## Cambios respecto al plan original

- **Migración más simple**: no hay tabla de respaldo. Todo valor guardado bajo 4007 proviene del tipo de importación que antes iba al id 34, así que `down()` mueve 4007 → 34 y elimina la fila; es equivalente y reversible sin estado extra.
- **Sin vínculo con la campaña**: el campo 4007 se dibuja aparte en la vista (no es hijo de un grupo), por lo que la migración no toca `campaign_customer_meta_data`.
- **Campaña dentro de la URL firmada**: `cid` viaja en la URL firmada del POST (el hidden `campaign_id` ya no existe en el formulario); solo los formularios abiertos antes del deploy lo envían en el cuerpo y se aceptan durante la transición.
- **Firma inválida**: una URL con `signature` alterada se rechaza siempre (403), incluso en el periodo de gracia; solo las URLs sin firma se toleran hasta `poe.legacy_links_until` (por defecto 2026-12-10, configurable con `POE_LEGACY_LINKS_UNTIL`).
- **Tests existentes**: los dos tests de almacenamiento de `PoeResidenciaEoriTest` se actualizaron para enviar un formulario completo (antes enviaban un cuerpo parcial que ya no es válido).
