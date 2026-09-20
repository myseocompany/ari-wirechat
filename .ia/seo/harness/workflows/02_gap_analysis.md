# Workflow — GAP_ANALYSIS

## Objetivo

Identificar dónde competir. Producir `content_gaps.csv` con score priorizado.

## Pasos

1. **Ranked keywords de rivales.**
   - `dataforseo_labs/google/ranked_keywords/live` para 4 dominios × 2 países = 8 requests.
   - Rivales: `ankofood.com`, `anko.com.tw`, `ferrero-machines.com`, `adlovermaquinas.com`, `empanadasmachine.com`.

2. **Intersecciones de dominio.**
   - `dataforseo_labs/google/domain_intersection/live` — Maquiempanadas vs cada rival, USA y COL.
   - Genera pares "keyword × dominios que rankean".

3. **Cruzar con universo.**
   - Merge de rivales rankings con `keywords_universe.csv`.
   - Cada keyword recibe columnas: `pos_maqui`, `pos_anko`, `pos_ferrero`, `pos_adlover`, `pos_empanadamachine`.

4. **Clasificar gap.**
   - **Total gap:** Maqui no aparece en top 20 y algún rival está en top 10.
   - **Position gap:** Maqui está en top 20 pero por debajo de al menos un rival top 5.
   - **Defense:** Maqui está en top 5 y algún rival está subiendo.
   - **Won:** Maqui está en top 3 y todos los rivales por debajo.

5. **Score y priorización.**
   - Fórmula: `score = volume × (max_rival_pos - maqui_pos) / max_rival_pos` — favorece gaps grandes en keywords con volumen.
   - Filtrar por `intent in [commercial, transactional]` (los informacionales van a un cluster secundario).
   - Ordenar descendente.

6. **Impressions-without-clicks (bonus, gratis).**
   - Leer `data/gsc/**/Consultas.csv`.
   - Filtrar filas con `impressions > 500` y `CTR < 2%` y `pos entre 4 y 10`.
   - Son "gaps de snippet" — no falta contenido, falta title/meta/schema. Se marcan en `content_gaps.csv` con `gap_type = snippet_win`.

7. **Impresiones vs. presencia en AI Overviews.**
   - Cruzar `data/maquiempanadas.com-Performance-on-Search-Generative-AI-Features-*/Consultas.csv` con `content_gaps.csv`.
   - Marcar las que ya aparecen en AI Overviews como `ai_ready=true` (menor riesgo de crear contenido en vano).

## Salidas verificables

- `content_gaps.csv` con ≥100 filas, columnas: `keyword`, `country`, `volume`, `pos_maqui`, `pos_top_rival`, `top_rival_domain`, `gap_type`, `score`, `intent`, `ai_ready`, `notes`.

## Techo de gasto en este paso

**$3 USD** de los $45 totales.

## No hacer

- No priorizar solo por volumen. Un keyword con volumen 200 y gap real convierte mejor que uno con 2,000 dominado por dominios domésticos.
- No incluir keywords con SERP dominado por retail masivo (Amazon, Walmart) — el score debe penalizarlas.
