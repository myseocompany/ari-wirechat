---
slug: customer-instagram-fields
report_started: 2026-10-25
report_completed: 2026-10-25
---

# Testing Report

## Verificadores automáticos

| Verificador | Comando | Resultado | Notas |
|---|---|---|---|
| Tests dirigidos | `php artisan test tests/Unit/CustomerInstagramFieldsTest.php` | PASS | 16 tests, 47 assertions, 0.26s |
| Suite Unit completa | `php artisan test --testsuite=Unit` | FAIL (preexistente) | 38 passed / 5 failed. Los 5 fallos son previos a este feature — ver abajo |
| Suite Feature | `php artisan test --testsuite=Feature` | NO EJECUTABLE (preexistente) | Pest aborta en la carga de la suite — ver abajo |
| Estilo PHP | `vendor/bin/pint --dirty` | PASS | 4 archivos revisados, 1 fix de estilo aplicado (`single_quote`) |
| Lint frontend | `npm run lint` | N/A | El script no existe en `package.json` (solo `dev` y `build`) |
| Typecheck frontend | `npm run typecheck` | N/A | El script no existe en `package.json` |
| Grafo actualizado | `graphify update .` | N/A | No existe `graphify-out/graph.json` en el repo |

### Desviación respecto a SPECBOOT

`SPECBOOT.md` pide rama `feature/<slug>`. Este feature se trabajó en el worktree ya existente `customer-instagram`, rama `myseocompany/customer-instagram`, por indicación explícita del solicitante ("commitea en la rama de este worktree"). Los artefactos quedan en `specs/active/customer-instagram-fields/` como corresponde.

### Nota sobre el entorno

`AGENTS.md` declara que el proyecto no trae entorno de test local y pide no correr `php artisan test` automáticamente. En esta sesión sí se pudo ejecutar la suite **Unit**: el worktree no traía `vendor/`, así que se copió (no symlink, para que el autoload resuelva contra este worktree y no contra el checkout principal) desde el checkout principal, cuyo `composer.lock` es idéntico. `vendor/` está en `.gitignore` y no entra al commit. **No se levantó ninguna base de datos y no se corrió ninguna migración contra ninguna base.**

### Fallos preexistentes de la suite Unit (no introducidos por este feature)

Ninguno toca archivos modificados por este cambio:

1. `CustomerActionFileUploadTest` — `MissingAppKeyException`. Falta `.env` / `APP_KEY` en local.
2. `CustomerFileUploadTest` — `MissingAppKeyException`. Misma causa.
3. `CustomerEditAssignedUserTest` — `ArgumentCountError`: el test construye `CustomerController` con 1 argumento y el constructor pide 2 (`CustomerService` + `MessageSourceConversationService`). Test desactualizado respecto al constructor actual.
4. `CustomerShowDetailsTest > includes sellerchat messages in the customer timeline` — espera `SellerChat` en `show_partials/actions_widget_wp.blade.php`; ese archivo no se tocó en este feature.
5. `DesignSystemHeaderTest` — espera textos en `design-system.blade.php`; archivo no tocado en este feature.

### Por qué los tests nuevos son Unit y no Feature

La suite **Feature** no arranca siquiera la fase de carga:

```
ERROR  Test case `Tests\TestCase` can not be used. The folder
`tests/Feature/CustomerActionTypeOrderTest.php` already uses the test case `Tests\TestCase`
```

Es un conflicto de configuración preexistente: `tests/Pest.php` aplica `uses(TestCase::class, RefreshDatabase::class)->in('Feature')` y además varios archivos de `tests/Feature` llaman `uses(TestCase::class)` por su cuenta.

Aunque la suite cargara, un test con `RefreshDatabase` no serviría: **no existe migración que cree la tabla `customers`** (la única relacionada, `2025_02_15_999999_document_customers_table.php`, es documentation-only). La tabla es legacy de producción, así que una base recién migrada no la tiene y no se puede instanciar un `Customer` persistido.

Por eso los tests nuevos siguen la convención que el repo ya usa para estos casos (`tests/Unit/CustomerShowDetailsTest.php`, `CustomerIndexFilterSidebarTest.php`): Pest sobre inspección estática del código fuente, más ejercicio del modelo en memoria (sin persistencia).

## Cobertura de criterios de aceptación

- [x] **AC1** — Migración no falla con `instagram_url` ya existente. Cubierto por: `tests/Unit/CustomerInstagramFieldsTest.php::it guards the migration so it can run on a base where the column already exists`.
- [x] **AC2** — `instagram_followers` como `UNSIGNED BIGINT NULL` sin índice ni default. Cubierto por: `::it declares both instagram columns as nullable and without indexes`.
- [x] **AC3** — Guarda `Schema::hasTable` para entornos sin tabla `customers`. Cubierto por: `::it guards the migration so it can run on a base where the column already exists`.
- [x] **AC4** — Campos en `$fillable` y cast a entero. Cubierto por: `::it exposes the instagram fields on the customer model` y `::it casts the followers count to an integer`.
- [x] **AC5** — Normalización a `https://instagram.com/<handle>`. Cubierto por: `::it normalizes the stored instagram value into a canonical profile url` (dataset de 7 formatos: handle suelto, `@handle`, con espacios, sin esquema, con esquema, con `www` y barra final, con query string) y `::it returns null instagram accessors when there is no value stored`.
- [x] **AC6** — Seguidores con separador de miles junto al enlace. Cubierto por: `::it shows the instagram link and followers inside the details block`.
- [x] **AC7** — Sin datos, el bloque no se renderiza. Cubierto por: `::it shows the instagram link and followers inside the details block` (verifica la condición `@if($instagramUrl || !is_null($model->instagram_followers))`).
- [x] **AC8** — El formulario y `update()` persisten ambos campos, con `null` cuando viene vacío. Cubierto por: `::it lets the edit form submit both instagram fields` y `::it persists both instagram fields on customer update`.

## Verificación manual (UI o integraciones externas)

No ejecutada en esta sesión: el entorno local no tiene base de datos ni `.env`, así que no se puede levantar la app con datos reales. Queda pendiente para el coordinador después del deploy:

- [ ] Abrir la ficha de un customer con `instagram_url` cargado → el enlace apunta a `https://instagram.com/<handle>` y abre en pestaña nueva.
- [ ] Abrir la ficha de un customer sin Instagram → el bloque "Instagram" no aparece y el resto de "Detalles" se ve igual que antes.
- [ ] Editar un customer, guardar handle + seguidores, volver al detalle → ambos valores aparecen; borrar el campo de seguidores y guardar → queda vacío, no "0".
- [ ] Verificar el render en móvil: el bloque usa `flex flex-wrap`, el badge debería bajar de línea sin romper el grid.

## Fallos encontrados y decisiones

- **Fallo 1**: la primera versión de `getInstagramHandle()` hacía `trim($handle, '/')` y después `ltrim($handle, '@')`, así que un valor como `@/evil.com` dejaba una barra inicial en el handle. Causa raíz: orden de limpieza. Decisión: fix aplicado — `ltrim($handle, '@/')` limpia ambos. Verificado con los 14 casos del script de validación. En ningún caso el enlace puede escapar del dominio `instagram.com`, porque la URL se arma concatenando el host fijo con el handle, nunca devolviendo el valor crudo.
- **Fallo 2**: `php artisan test --filter=...` no funciona en este repo (el `--filter` carga toda la suite y Feature aborta en la carga). Decisión: correr por ruta de archivo — `php artisan test tests/Unit/CustomerInstagramFieldsTest.php`. El comando por ruta queda documentado aquí porque el `/verify` de `SPECBOOT.md` asume `--filter`.

## Áreas NO cubiertas

- **Ejecución real de la migración.** Por regla explícita del pedido no se corrió `migrate` contra ninguna base, ni siquiera local. Las guardas están verificadas por lectura del código, no por ejecución. El riesgo residual es bajo (`hasTable`/`hasColumn` son API estándar de Laravel), pero no es cero: nadie ha visto la migración correr.
- **Comportamiento con `instagram_url` apuntando a un post** (`instagram.com/p/<id>`) en vez de a un perfil: se normalizaría a `https://instagram.com/p`, que es un enlace roto. Decidido fuera de alcance (supuesto marcado `[CONFIRMAR]` en `02-refined.md`); se asume que la columna guarda perfiles.
- **Render real en navegador.** Las aserciones de vista son estáticas sobre el Blade, no compilan la plantilla ni verifican el layout. El balance de directivas se chequeó aparte (`@if`/`@endif` cuadran en ambas vistas), pero no sustituye a abrir la página.
- **Formulario de creación y alta por API**: no incluyen los campos nuevos. Fuera de alcance declarado.

## Estado final

- [x] Todos los verificadores automáticos aplicables en verde (los fallos de la suite Unit son preexistentes y están identificados uno por uno; `npm`/graphify no aplican a este repo).
- [x] Todos los AC cubiertos por tests; la verificación manual pendiente está documentada arriba.
- [x] Auto-review sin observaciones bloqueantes.
- [x] Proposal sin divergencias respecto al plan.
