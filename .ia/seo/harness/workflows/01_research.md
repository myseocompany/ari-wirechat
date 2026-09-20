# Workflow — RESEARCH

## Objetivo

Construir el universo semántico + benchmark cross-category antes de proponer contenido.

## Pasos

1. **Definir semillas.** Máximo 10 keywords cabeza (ES + EN) alineadas al catálogo real de la cliente.

2. **Expandir con DataForSEO Labs.**
   - `keyword_ideas/live` sobre cada semilla.
   - `keyword_suggestions/live` para variantes long-tail.
   - `related_keywords/live` para vecinos semánticos.
   - Salida: universo raw en `data/dataforseo/YYYY-MM-DD_keyword_ideas_*.json`.

3. **Enriquecer con volumen y dificultad.**
   - `keywords_data/google_ads/search_volume/live` por país+idioma.
   - `dataforseo_labs/google/bulk_keyword_difficulty/live` por batch.
   - Salida agregada: `keywords_universe.csv` con columnas `keyword`, `country`, `language`, `volume`, `cpc`, `competition`, `keyword_difficulty`, `intent`, `parent_topic`.

4. **Clasificar intent.**
   - `dataforseo_labs/google/search_intent/live` sobre las top 200 por volumen.
   - Categorías: `informational`, `commercial`, `transactional`, `navigational`.

5. **Benchmark cross-category (Julian Goldie insight).**
   - Elegir 5 nichos B2B análogos con producto físico caro: tortillas industriales, sushi/gyoza, panadería industrial, café comercial, helado industrial.
   - Recorrer 10 SERPs por nicho × 2 países = 100 SERPs con `serp/google/organic/live/advanced` (`depth=30`).
   - Auditar top 3 URLs de cada SERP con `on_page/instant_pages` (30 páginas seleccionadas).
   - Salida: `benchmark_categories.md` con patrones ganadores (formato de pillar, uso de schema, cluster estrategias, formatos de imagen, formatos de video, cómo aparecen en AI Overviews).

## Salidas verificables

- `keywords_universe.csv` con ≥500 filas.
- `benchmark_categories.md` con ≥5 nichos analizados.
- Fila en `COSTS.csv` por cada llamada.

## Techo de gasto en este paso

**$20 USD** de los $45 totales del feature.

## No hacer

- No hacer keyword research desde cero cada semana. La lista se congela por 3-6 meses; los cambios ocurren en `EVOLVE`, no aquí.
- No proponer contenido en este paso — separación de fases estricta.
