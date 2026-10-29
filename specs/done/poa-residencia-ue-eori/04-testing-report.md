---
slug: poa-residencia-ue-eori
report_started: 2026-10-29
report_completed: 2026-10-29
---

# Testing Report

## Verificadores automáticos

Corrida `/verify` del 29/10/2026 en el worktree `feature/poa-residencia-ue-eori`.

| Verificador | Comando | Resultado | Notas |
|---|---|---|---|
| Tests dirigidos | `php artisan test tests/Unit/PoeResidenciaEoriTest.php` | PASS | 11 tests, 53 assertions, 0.33s. SQLite en memoria |
| Suite Unit completa | `php artisan test --testsuite=Unit` | FAIL (preexistente) | 48 passed / 5 failed. Son exactamente los 5 fallos ya documentados en `customer-instagram-fields` (MissingAppKey ×2, ArgumentCountError, SellerChat, DesignSystemHeader); ninguno toca archivos de este feature |
| Suite Feature | `php artisan test --testsuite=Feature` | NO EJECUTABLE (preexistente) | Pest aborta por `uses()` duplicado en `tests/Feature/CustomerActionTypeOrderTest.php` |
| Lint frontend | `npm run lint` | N/A | El script no existe en `package.json` |
| Typecheck frontend | `npm run typecheck` | N/A | El script no existe en `package.json` |
| Grafo actualizado | `graphify update .` | N/A en worktree | `graphify-out/graph.json` no existe en el worktree (solo en el checkout principal); correr tras el merge |

## Cobertura de criterios de aceptación

- [x] **AC1** — Pregunta de residencia obligatoria con dos opciones. `::AC1` + navegador (formulario inválido sin responder).
- [x] **AC2** — Con "No" el formulario queda como hoy. Navegador escenario 1.
- [x] **AC3** — Aviso y pregunta EORI con texto literal. `::AC3/AC4` + navegador escenario 2.
- [x] **AC4** — Segundo aviso con EORI = No. `::AC3/AC4` + navegador escenario 3.
- [x] **AC5** — Sin segundo aviso con EORI = Sí. Navegador escenario 4.
- [x] **AC6** — Cambio Sí→No oculta y no envía EORI. Navegador escenario 5 + `::AC9`.
- [x] **AC7** — Envío aceptado sin EORI y sin notificaciones. `::AC7/AC8`.
- [x] **AC8** — Se guardan residencia y EORI. `::AC7/AC8`.
- [x] **AC9** — Con residencia = No solo se guarda residencia. `::AC9`.
- [x] **AC10** — Respuestas visibles en acordeón POA. `::AC10/AC11`.
- [x] **AC11** — Envíos antiguos sin cambios. `::AC10/AC11`.
- [x] Orden destino < residencia < veracidad. `::places the residence block…`.
- [x] Otras campañas no muestran el bloque. `::does not show the residence block…`.
- [x] Migración idempotente y rollback sin borrar respuestas. `::is idempotent…`, `::rolls back…`.
- [x] Footer de contacto (alcance añadido). `::shows the Maquiempanadas contact details…`.

## Verificación manual (UI o integraciones externas)

Chrome headless (390px de ancho, móvil) dirigido por CDP sobre el HTML real renderizado por `MetaDataController::createPoe` con datos ficticios (cliente "Cliente Demo"), jQuery y Bootstrap desde CDN. Se lee visibilidad, `disabled`, `checkValidity()` y el `FormData` que enviaría el formulario:

- [x] Inicio (Individual): bloque EORI oculto, formulario inválido hasta responder residencia (AC1).
- [x] Residencia No: bloque oculto, EORI deshabilitado, envía solo `meta_4001=No`, formulario válido (AC2).
- [x] Residencia Sí: aviso y pregunta visibles, EORI habilitado y obligatorio (inválido sin responder) (AC3).
- [x] Sí + EORI No: aviso y recordatorio visibles, envía ambos, válido (AC4, AC7).
- [x] Sí + EORI Sí: recordatorio oculto, válido (AC5).
- [x] Cambio Sí→No: todo oculto, envía solo `meta_4001=No` (AC6).
- [x] Individual: grupo 14 oculto y bloque EORI visible; Sociedad: grupo 14 visible.
- [x] Captura móvil revisada: aviso amarillo, recordatorio rojo, footer legible.
- [ ] Safari iOS real: no probado (solo Chrome headless).

## Fallos encontrados y decisiones

- Ninguno en `/verify`. Los fallos de suite son preexistentes (ver tabla).

- **Fallo en producción (deploy del 29/10/2026, 14:46 UTC)**: `php artisan migrate` falló con `SQLSTATE[HY000] 1364 Field 'id' doesn't have a default value` al insertar en `campaign_customer_meta_data`. Causa raíz: en producción esa tabla tiene `id int NOT NULL` como PRIMARY KEY **sin AUTO_INCREMENT** (`SHOW CREATE TABLE`), y el test usaba `$table->id()` (autoincremental), así que no lo detectó. Estado que quedó: filas 4000–4006 creadas y `weight` de los grupos aplicados, pivote sin crear, migración `Pending`; el formulario siguió funcionando sin el bloque. Decisión: la migración asigna el `id` del pivote explícitamente (`max(id) + 1`) y el test ahora crea el pivote como en producción (`id` sin autoincremento). Sin el arreglo, el test reproduce el mismo error (`NOT NULL constraint failed`). Como `up()` es idempotente, volver a correrla completa lo que faltó. Rama `fix/poe-eori-pivot-id`.

### `/code_review` (29/10/2026)

| Eje | Marca | Justificación |
|---|---|---|
| Fidelidad al proposal | OK | Código = plan; divergencias (test en Unit, constantes, parcial del aviso, JS en parcial, footer) documentadas en `03-proposal.md` |
| Cobertura de AC | OK | AC1, AC3, AC4 y AC7–AC11 con assertions reales; la parte JS de AC2–AC6 se cubrió con el navegador (CDP), documentado. Sin `->todo()` |
| Multi-tenancy | OK | No hay `tenant_id` en este dominio. Todo se limita a la campaña 40, y hay un test que comprueba que otras campañas no ven el bloque |
| Seguridad | OBS | Sin superficie nueva: Blade escapa con `{{ }}`, `@json` para JS, sin parámetros nuevos. La obligatoriedad de AC1 es solo del lado del cliente (igual que el resto del formulario). El endpoint conserva las vulnerabilidades preexistentes → `fix/poe-endpoint-hardening` |
| Efectos colaterales | OBS | `createPoe` ahora ordena por `weight, id` para cualquier campaña que abra el POE (38/44/45 no lo usan en la práctica). El deploy ejecuta una migración de datos en producción → requiere aprobación |
| Deuda técnica | OBS | Los valores "Sí" / "No, aún no…" están en la migración y en las constantes del modelo (duplicados a propósito para que la migración sea autocontenida). El footer tiene datos de contacto de MQE fijos en la vista (el endpoint ya es exclusivo de MQE) |

Reglas de `AGENTS.md`:
- FAQ (`resources/views/public/faq.blade.php`, `docs/FAQ_ARICRM.md`): N/A, los archivos no existen en el repo.
- Snapshot de `system_prompt`: N/A, no se tocan prompts.
- UI de `/settings/*`: N/A.
- Docs de tenant (`docs/mqe/`): N/A, no se tocaron.

Observaciones priorizadas (ninguna bloqueante):
1. **Aprobación del deploy**: la migración escribe en `customer_meta_datas`, `campaign_customer_meta_data` y en los `weight` de los grupos 14/20/28/34 de producción. Correrla solo con OK explícito y verificar con una lectura posterior.
2. **Obligatoriedad solo en el cliente**: si alguien envía el POST sin residencia, se acepta (comportamiento actual del formulario). Atenderlo dentro de `fix/poe-endpoint-hardening`.
3. **Safari iOS** no se probó en un dispositivo real.
4. **Datos de contacto fijos** en `POE/partials/footer.blade.php`: si cambian, hay que editar la vista.
5. `updateOrInsert` en la migración sobrescribe `created_at` si se vuelve a correr (impacto nulo).

## Áreas NO cubiertas

- Safari iOS en dispositivo real.
- La migración no se ha corrido contra MySQL real; se probó en SQLite. Se correrá en producción solo con aprobación explícita.
- Seguridad del endpoint (subida de archivos, escritura en cualquier cliente): fuera de alcance, spec `fix/poe-endpoint-hardening`.

## Estado final

- [x] Todos los verificadores automáticos en verde. (Verificadores declarados en `SPECBOOT.md`: tests dirigidos PASS; lint/typecheck N/A; graphify N/A en el worktree. Los fallos de la suite completa son preexistentes y están documentados arriba.)
- [x] Todos los AC cubiertos por tests o verificación manual documentada.
- [x] `/code_review` sin observaciones bloqueantes.
- [x] Proposal actualizada si hubo divergencias.
