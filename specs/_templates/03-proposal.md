---
slug: <feature-slug>
proposed_at: <YYYY-MM-DD>
---

# Proposal — Plan técnico

## Resumen

En 2-3 líneas: qué se hace y cómo.

## Cambios propuestos

Lista de archivos y componentes a crear/modificar. No pegar código — solo la topología del cambio.

- `app/Models/<Modelo>.php` — <razón>
- `app/Services/<Servicio>.php` — <razón nuevo servicio o modificación>
- `database/migrations/<timestamp>_<nombre>.php` — <qué tabla/columna>
- `resources/js/Pages/<Vista>.tsx` — <qué UI>
- `tests/Feature/<Test>.php` — <qué escenarios>

## Contratos afectados

APIs públicas, endpoints, eventos, jobs, resources, prompts de agentes IA. Detallar cambios de firma, payload o comportamiento.

- Endpoint `POST /api/v1/...`: <antes> → <después>
- Evento `X`: <nuevo payload>
- Ninguno / No aplica

## Alternativas descartadas

Al menos una opción que se consideró y por qué no se eligió. Sin esto, el proposal es un "aquí está la solución" y no un plan.

- **Alternativa A**: <descripción>. Descartada porque <razón>.
- **Alternativa B**: <descripción>. Descartada porque <razón>.

## Plan de tests

Qué tests se van a escribir. Deben cubrir todos los AC del `02-refined.md`.

- **Feature**: `tests/Feature/<Test>.php`
  - <cubre AC1, AC2>
- **Unit**: `tests/Unit/<Test>.php`
  - <cubre lógica interna>
- **Regresión / manual**: <si aplica, qué se verifica en UI>

## Impacto operativo

- **Migraciones**: reversibles / no reversibles. Plan de rollback.
- **Feature flags**: si se protege el rollout con flag.
- **Datos existentes**: qué pasa con lo que ya está en BD.
- **Tenants**: si aplica solo a algunos, listar; si es global, decirlo.
- **Documentación**: qué se actualiza en `docs/`, `resources/views/public/faq.blade.php`, `docs/FAQ_ARICRM.md`.

## Estimación

- **Complejidad**: baja / media / alta.
- **Tiempo estimado LLM**: <horas de trabajo agéntico>.
- **Riesgo de regresión**: bajo / medio / alto — con áreas específicas.

## Cambios respecto al plan original

*(Esta sección se rellena durante `/apply` si la realidad diverge del plan. No borrar el plan original — anexar lo que cambió y por qué.)*
