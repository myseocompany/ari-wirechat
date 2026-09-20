# Workflow — EVOLVE

## Objetivo

Feedback loop: usar los resultados del MONITOR para ajustar plan y capturar quick-wins.

## Cadencia

Mensual, después de generar el `monitoring/YYYY-MM/report.md`.

## Pasos

1. **Impressions-without-clicks (Julian Goldie insight).**
   - Leer `data/gsc/**/Consultas.csv` últimos 30 días.
   - Filtrar: `impressions > 500` AND `CTR < 2%` AND `pos entre 4 y 10`.
   - Estas keywords están rankeando pero el snippet está perdiendo el clic. Solución barata:
     - Reescribir title (Haiku, batch).
     - Reescribir meta description (Haiku, batch).
     - Considerar agregar structured data si aplica.
   - Registrar cada intervención en `content_ledger.md` como pieza tipo `snippet_optimization`.

2. **Cambios de rivales.**
   - Comparar ranked_keywords de rivales mes vs mes.
   - Detectar keywords donde Anko/Ferrero/adlovermaquinas subieron o bajaron fuerte.
   - Si un rival subió mucho en un gap nuestro → estudiar por qué (nueva URL, backlink recibido, cambio de estrategia).

3. **Rebench de gaps.**
   - Correr `dataforseo_labs/google/domain_intersection/live` sobre nuevas URLs publicadas.
   - Actualizar `content_gaps.csv`:
     - Marcar gaps cerrados (`status=won`) — Maqui ya rankea top 3.
     - Marcar gaps abandonados (posición no mejora en 3 meses después de publicar → analizar por qué, considerar rewrite).
     - Detectar gaps nuevos que aparecen.

4. **Ajustar content plan.**
   - Sprint siguiente prioriza los gaps que ganaron score en el rebench.
   - Piezas de bajo rendimiento (publicadas hace 90 días, sin subir de posición) van a **revisión** — no borrar, mejorar.

5. **Actualizar SKILL files si aprendemos.**
   - Si el checklist QC falla siempre en el mismo punto → mejorar el checklist o el prompt.
   - Si un tipo de contenido no rankea → ajustar el prompt template.

## Salidas verificables

- Lista de snippet optimizations en `content_ledger.md` (batch mensual).
- `content_gaps.csv` actualizado con nuevos scores y statuses.
- Sprint siguiente definido en `content_plan.md`.

## Techo de gasto en este paso

**$1-2 USD/mes** para rebench de intersecciones.

## No hacer

- No reescribir piezas cada mes por ansiedad. Un contenido tarda 3-6 meses en asentarse. Solo intervenir si hay señal clara.
- No cambiar la estrategia entera cada mes. Ajustes incrementales.
- No ignorar los quick-wins de impressions-without-clicks — son la fruta baja, casi siempre subes 30-50% clics con reescribir title + meta.
