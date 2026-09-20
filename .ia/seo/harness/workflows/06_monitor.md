# Workflow — MONITOR

## Objetivo

Medir semanal + mensualmente en las 4 categorías universales (web, imágenes, video, shopping) por país.

## Cadencia

### Semanal (automatizado)

Script Python en cron corre cada lunes 09:00:

1. **SERP snapshot web** — 30 keywords principales × 2 países = 60 requests.
   - `serp/google/organic/live/advanced` con `depth=20`.
   - Extrae posición del sitio + top 3 rivales.
2. **SERP snapshot imágenes** — 10 keywords principales × 2 países = 20 requests.
   - `serp/google/images/live/advanced`.
3. **Snapshot Shopping** — 5 keywords cabeza × 2 países.
   - `serp/google/organic/live/advanced` extrayendo bloque `popular_products`.

**Costo estimado semanal: $0.50-0.80 USD.**

Guarda en `monitoring/YYYY-WW/snapshot.json`.

### Mensual (semi-automatizado + revisión humana)

Primer lunes de cada mes:

1. Descargar GSC exports (Web / Imagen / Vídeo / AI Features) para últimos 30 días.
2. Correr `dataforseo_labs/google/historical_rank_overview/live` para dominio + 3 rivales.
3. Generar `monitoring/YYYY-MM/report.md` con:
   - Deltas vs mes anterior por keyword del set.
   - Nuevas keywords que aparecen en top 20 (posibles quick wins).
   - Keywords que salieron del top 20 (alerta).
   - Comparativa con rivales.
4. Revisión humana: Nicolás lee el report y decide ajustes al plan.

**Costo estimado mensual: $3-5 USD.**

### Trimestral (rebench profundo)

Cada 3 meses:

1. Rebench presencia en LLMs: 10 queries × 4 LLMs = 40 llamadas.
   - `ai_optimization/{chat_gpt|claude|gemini|perplexity}/llm_responses/live`.
2. Rebench de rivales: cambios en su ranked_keywords.
3. Actualizar `analisis_keywords_gsc.md` con hallazgos.
4. Actualizar `reporte_maquiempanadas.html` para la cliente.

**Costo estimado trimestral: $5 USD.**

## Alertas

El monitoreo dispara alerta cuando:

- Una URL top 3 pierde 3+ posiciones vs semana anterior → alerta amarilla.
- Una URL top 3 pierde 5+ posiciones o sale del top 10 → alerta roja + pausar publicación de contenido nuevo hasta diagnosticar.
- El sitio pierde >20% de impresiones agregadas vs promedio de 4 semanas → alerta roja (posible penalización).
- Un rival aparece de golpe en top 3 en gap identificado → alerta informativa.

## Salidas verificables

- `monitoring/YYYY-WW/snapshot.json` cada semana.
- `monitoring/YYYY-MM/report.md` cada mes.
- `monitoring/YYYY-Q/rebench_llm.md` cada trimestre.

## No hacer

- No aumentar frecuencia semanal más allá de lunes 09:00 — un snapshot por semana es suficiente para tendencias.
- No comparar snapshots individuales (ruido de day-of-week). Comparar ventanas de 4 semanas.
- No borrar snapshots viejos — el histórico es el activo más valioso a los 6-12 meses.
