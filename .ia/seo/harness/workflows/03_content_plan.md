# Workflow — CONTENT_PLAN

## Objetivo

Traducir `content_gaps.csv` en un plan concreto de piezas a producir.

## Pasos

1. **Agrupar gaps en topic clusters.**
   - Cada cluster tiene 1 pillar page + 3-6 satellite posts.
   - Ejemplo: cluster "empanadas industriales" → pillar `Máquinas industriales para hacer empanadas` + satellites (cuánto cuesta, cómo elegir modelo, comparativa entre modelos, cuánto produce por hora, etc.).

2. **Asignar piezas a formato.**
   - Gap comercial de alta prioridad + producto único → **pillar page**.
   - Gap con intent informacional que refuerza un pillar → **cluster post**.
   - Gap local (ciudad + producto) → **local landing** con LocalBusiness schema.
   - Gap con PAA marcada → considerar responder en **product FAQ** existente.
   - Video existente en YouTube con gap match → reescribir descripción SEO + crear post que embebe el video.

3. **Asignar prioridad y ventana.**
   - **Sprint 1 (semanas 4-8):** 6 piezas — los gaps con mayor score comercial.
   - **Sprint 2 (semanas 9-14):** 5 piezas — expansión hacia adyacencias (arepas, moldes, desmechadora).
   - **Sprint 3 (semanas 15-20):** 4 piezas — locales + defense de posiciones.

4. **Validar contra benchmark cross-category.**
   - Para cada pieza propuesta, buscar en `benchmark_categories.md` el patrón ganador del nicho análogo (¿cómo hace Anko su pillar de pierogi machine?, ¿cómo hacen los de tortillas industriales?).
   - Adaptar el patrón al contexto Maquiempanadas.

5. **Asignar activos originales requeridos.**
   - Cada pieza declara qué necesita de Maquiempanadas: fotos originales (¿cuáles?), video (¿existe o hay que grabar?), testimonio (¿de qué cliente?), datos técnicos (¿ficha oficial?).
   - Sin activos, la pieza no arranca — se pide antes de asignar redactor humano.

6. **Registrar en Content Ledger.**
   - Agregar filas a `../content_ledger.md` con estado `draft`.

## Salidas verificables

- `content_plan.md` con:
  - 15+ piezas asignadas a gaps de `content_gaps.csv`.
  - Cada pieza con: tipo, sprint, target keyword, país, categoría, referencia a benchmark, activos requeridos, autor asignado.
- Filas correspondientes en `content_ledger.md`.

## Techo de gasto en este paso

**$0** (todo es análisis sobre datos ya en el repo). No hay consultas a DataForSEO en este paso.

## No hacer

- No agregar piezas al plan sin gap en `content_gaps.csv`.
- No planear 30 piezas de golpe. Sprint 1 con 6 es realista, si va bien se levanta el ritmo. Sobre-planeación quema al equipo.
- No mezclar clusters. Cada cluster tiene un pillar. Si dos gaps mapean al mismo pillar, uno se hace primero y el otro después.
