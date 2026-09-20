---
slug: rankear-2026
report_started: 2026-09-20
report_completed: <YYYY-MM-DD o vacío>
---

# Testing Report

## Verificadores automáticos

| Verificador | Comando | Resultado | Notas |
|---|---|---|---|
| Tests dirigidos | `php artisan test --filter=<slug>` | PASS/FAIL | <link a run o nº de tests> |
| Suite completa | `php artisan test` | PASS/FAIL | <si se corrió> |
| Lint frontend | `npm run lint` | PASS/FAIL | |
| Typecheck frontend | `npm run typecheck` | PASS/FAIL | |
| Grafo actualizado | `graphify update .` | PASS/N/A | <si existe graphify-out/> |

## Cobertura de criterios de aceptación

Marcar cada AC del `02-refined.md`:

- [ ] **AC1** — <título AC>. Cubierto por: `tests/Feature/<Test>.php::<test method>`.
- [ ] **AC2** — <título AC>. Cubierto por: <archivo::método>.

## Verificación manual (UI o integraciones externas)

Cuando aplique. Documentar navegador/dispositivo/tenant usado.

- [ ] Escenario 1: <descripción>. Resultado: <ok / falló / se corrigió>.
- [ ] Escenario 2: ...

## Fallos encontrados y decisiones

Cualquier fallo durante `/verify` o `/code_review` que llevó a `implement changes`:

- **Fallo 1**: <descripción>. Causa raíz: <análisis>. Decisión: <fix aplicado / diferido con justificación>.
- **Fallo 2**: ...

## Áreas NO cubiertas

Sinceridad total: qué queda sin probar y por qué.

- <ej. "Escenario de concurrencia real requiere carga sintética, no cubierto">.

## Estado final

- [ ] Todos los verificadores automáticos en verde.
- [ ] Todos los AC cubiertos por tests o verificación manual documentada.
- [ ] `/code_review` sin observaciones bloqueantes.
- [ ] Proposal actualizada si hubo divergencias.

Solo cuando las 4 cajas están marcadas, el feature pasa a `FEATURE READY` y se puede correr `/archive`.
