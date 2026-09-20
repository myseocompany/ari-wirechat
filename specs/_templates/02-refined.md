---
slug: <feature-slug>
refined_at: <YYYY-MM-DD>
---

# Refined User Story

## Actor
Quién ejecuta la acción y quién se beneficia. Distinguir si son la misma persona o no.

## Valor
Problema real que se resuelve. Cuantificar cuando sea posible (tiempo ahorrado, error evitado, ingreso protegido).

## Criterios de aceptación

Formato Gherkin. Cada criterio verificable programáticamente en `/verify`.

- **AC1** — Given <precondición>, When <acción>, Then <resultado observable>.
- **AC2** — ...

## Requisitos no funcionales

- **Performance**: latencia esperada, carga soportada.
- **Seguridad**: autenticación, autorización, tenancy, PII.
- **Multi-tenancy**: cómo se garantiza aislamiento por `tenant_id`.
- **Observabilidad**: qué se loggea, qué métricas se emiten.
- **i18n / accesibilidad**: si aplica.
- **Compatibilidad**: móvil, navegadores, versiones.

## Supuestos

Todo lo que el LLM está asumiendo y NO está confirmado por el solicitante. Marcar cada uno con `[CONFIRMAR]` para forzar validación antes de `/propose`.

- [CONFIRMAR] Supuesto 1
- [CONFIRMAR] Supuesto 2

## Riesgos

- **Blast radius**: qué se rompe si sale mal.
- **Reversibilidad**: cuán fácil es deshacer el cambio.
- **Dependencias externas**: qué se necesita de terceros o de otros equipos.
- **Impacto en tenants activos**: quiénes ya usan la funcionalidad afectada.

## Fuera de alcance

Explícito. Lo que NO se hace en este feature (para prevenir scope creep).

- Fuera 1
- Fuera 2
