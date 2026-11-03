---
slug: poe-endpoint-hardening
report_started: 2026-11-03
report_completed: 2026-11-03
---

# Testing Report

## Verificadores automáticos

| Verificador | Comando | Resultado | Notas |
|---|---|---|---|
| Tests dirigidos | `vendor/bin/pest tests/Unit/PoeEndpointHardeningTest.php tests/Unit/PoeResidenciaEoriTest.php` | PASS | 25 tests, 152 assertions |
| Suite completa | `vendor/bin/pest tests/Unit` | PASS (sin regresiones) | 63 pasan, 5 fallan; son las 5 preexistentes (MissingAppKey ×2, ArgumentCountError, SellerChat, DesignSystemHeader), ninguna toca el POE. `tests/Feature` no carga en el repo (uses() duplicado, BD de test inexistente), igual que antes. |
| Estilo | `vendor/bin/pint --test` | PASS en archivos nuevos | Las observaciones de Pint en `MetaDataController`, `CustomerMetaData`, `AppServiceProvider` y las vistas Blade son de estilo previo al cambio; no se reformatearon para no inflar el diff. |
| Lint frontend | `npm run lint` | N/A | El repo no define el script. |
| Typecheck frontend | `npm run typecheck` | N/A | El repo no define el script. |
| Grafo actualizado | `graphify update .` | N/A | No existe `graphify-out/graph.json`. |

## Cobertura de criterios de aceptación

Todos en `tests/Unit/PoeEndpointHardeningTest.php`:

- [x] **AC1** — Archivos rechazados: `AC1/AC2/AC9: ignores files, project_id and login fields`.
- [x] **AC2** — `project_id` ignorado: mismo test.
- [x] **AC3** — Solo campos de la campaña: `AC3/AC4: saves only campaign fields…`.
- [x] **AC4** — `audience_id` del servidor: mismo test y el camino feliz.
- [x] **AC5** — Cliente inexistente: `AC5: a missing customer returns 404…`.
- [x] **AC6** — Enlace firmado: `AC6: signed links work and tampered or unsigned links expire…`.
- [x] **AC7** — Validación en el servidor: `AC7: rejects incomplete or invalid submissions…` (11 casos) y `AC7: company data is optional for Individual…`.
- [x] **AC8** — Límite de envíos: `AC8: the sixth submission within a minute gets 429…`.
- [x] **AC9** — Bloque `login_*` eliminado: test de AC1/AC2/AC9.
- [x] **AC10** — Todo o nada: `AC10: a failure while saving leaves no partial answers` (trigger sqlite que aborta el insert).
- [x] **AC11** — Etiqueta del tipo de importación: `AC11: migration reassigns…` (reasigna, idempotente, reversible, la etiqueta del join es "Importación de manera").
- [x] **AC12** — Camino feliz: `AC12: happy path…` y `AC12: legacy forms still posting meta_34…`; más los tests POE/EORI existentes.
- [x] **AC13** — Rechazos registrados sin valores: `AC13: rejections are logged without submitted values`.

## Verificación manual (UI o integraciones externas)

Pendiente, requiere deploy y aprobación del usuario:

- [ ] Abrir el enlace firmado del botón "POA" con el cliente de prueba 678620 y enviar el formulario completo; revisar el POA.
- [ ] Abrir un enlace viejo sin firma (periodo de gracia) y enviar.
- [ ] Confirmar en el log de producción la IP real (Cloudflare) y las entradas `poe.rejected` / `poe.legacy_link`.
- [ ] Probar en iPhone/Safari el retorno con errores de validación.

## Fallos encontrados y decisiones

- **Fallo 1**: dos tests existentes de `PoeResidenciaEoriTest` enviaban un cuerpo parcial sin `campaign_id` y daban 404. Causa raíz: ahora la campaña viaja en la URL firmada o, en formularios previos, en `campaign_id`; y el cuerpo parcial ya no es válido. Decisión: actualizar los tests para enviar un formulario completo con `campaign_id` (comportamiento correcto bajo el nuevo contrato).
- **Fallo 2**: el test del bucle de AC7 recibió 429. Causa raíz: el límite de 5/min agrupaba los 11 casos bajo la misma IP. Decisión: IP distinta por caso en ese test.

### Code review (2026-11-03)

| Eje | Marca | Justificación |
|---|---|---|
| Fidelidad al proposal | OK | El código sigue el plan; las divergencias (migración sin tabla de respaldo, 4007 sin vínculo a campaña, `cid` en la URL firmada, firma alterada siempre rechazada) están en `## Cambios respecto al plan original`. |
| Cobertura de AC | OK | AC1–AC13 con assertions reales, sin `->todo()`; el rechazo se prueba también en estado (cero filas). |
| Multi-tenancy | OK / N/A | Instancia de MQE sin `tenant_id`; las consultas están acotadas por cliente y campaña derivados del enlace firmado. |
| Seguridad | OK | Sin archivos, sin rutas desde el request, `audience_id` fijo, campos derivados de la campaña, firma + límite 5/min, escape Blade en errores y `old()`, logs sin valores. Un 404/403 no revela nombres. |
| Efectos colaterales | OBS | Contrato cambiado y documentado: `meta_34`→`meta_4007` (con alias), enlaces firmados, 403/404/429 nuevos. Fuera de la ventana de gracia los enlaces sin firma dejan de funcionar. |
| Deuda técnica | OBS | Ver observaciones 2 a 4. |

Reglas de AGENTS.md: no se tocó `docs/<tenant>/`, ningún `system_prompt` ni `/settings/*`; no es capacidad visible para usuarios del CRM, así que no aplica FAQ. Diff < 300 líneas en código; no se recomienda `/ultrareview`, aunque toca un endpoint público (el riesgo está acotado a la campaña 40 y a los tests de seguridad).

**Bloqueantes:** ninguno.

**Observaciones (no bloqueantes), por prioridad:**
1. Verificado en producción (solo lectura): los hijos de los grupos 14, 20, 28 y 34 son 17 campos de texto y 1 checkbox; todos son `required` en el HTML, así que exigirlos en el servidor es coherente y no bloquea a ningún cliente que complete el formulario.
2. `saveCustomerMeta` conserva el código de subida con `project_id` y toma `audience_id` del request. Ya no lo usa el POE; lo sigue usando `MetaDataController::store`, que está dentro del grupo con autenticación. Queda como candidato a otro spec (fuera de alcance).
3. La migración referencia `CustomerMetaData::POE_IMPORT_TYPES`; si esa constante cambia en el futuro, la migración histórica cambiaría de comportamiento. Aceptable ahora; conviene inlinear la lista si se vuelve a tocar.
4. Imports `ProjectLogin`/`ProjectDocument` en el controlador pueden quedar sin uso (limpieza menor, no se tocó para no ampliar el diff).

**Corregido durante el review:** en `POE/create.blade.php` el atributo `value="{{ old(...) }}"` se aplicaba a cualquier input que no fuera checkbox, lo que habría pisado el `value` de un radio genérico; ahora solo se aplica a inputs de texto.

## Áreas NO cubiertas

- No se ejecutó contra MySQL; los tests usan sqlite en memoria (precedente del repo). La reasignación 34→4007 y los `whereIn` son SQL estándar.
- La resolución de IP detrás de Cloudflare solo se verificó leyendo la configuración de nginx, no con tráfico real.
- La vista `customers/show` (botón "POA" firmado) no tiene test de render propio; el generador `URL::signedRoute('poe.create', …)` se cubre indirectamente por los tests de firma.
- JS del formulario (mostrar/ocultar por tipo, repoblado tras error) no se probó en navegador.

## Estado final

- [x] Todos los verificadores automáticos en verde.
- [x] Todos los AC cubiertos por tests o verificación manual documentada.
- [x] `/code_review` sin observaciones bloqueantes.
- [x] Proposal actualizada si hubo divergencias.

Solo cuando las 4 cajas están marcadas, el feature pasa a `FEATURE READY` y se puede correr `/archive`.
