# SEO Harness — MÉTODO

## Ciclo

```
RESEARCH → GAP_ANALYSIS → CONTENT_PLAN → GENERATE → PUBLISH → MONITOR → EVOLVE
                                                                          ↓
                                                    (feedback a RESEARCH cada ciclo)
```

Cada paso tiene su workflow en `workflows/`:

| Paso | Workflow | Salida verificable |
|---|---|---|
| **RESEARCH** | `workflows/01_research.md` | `keywords_universe.csv`, `benchmark_categories.md` |
| **GAP_ANALYSIS** | `workflows/02_gap_analysis.md` | `content_gaps.csv` con score `volumen × posición_rival` |
| **CONTENT_PLAN** | `workflows/03_content_plan.md` | `content_plan.md` con 15+ piezas asignadas a gaps |
| **GENERATE** | `workflows/04_generate.md` | Drafts en `../content/YYYY-MM-DD_slug/draft.md` |
| **PUBLISH** | `workflows/05_publish.md` | Pieza en producción + `trajectory.md` + submit a Indexing API |
| **MONITOR** | `workflows/06_monitor.md` | Snapshots semanales en `../monitoring/YYYY-WW/` |
| **EVOLVE** | `workflows/07_evolve.md` | Ajustes basados en impresiones-sin-clics + rebench de LLMs |

## Métricas por categoría universal

Cada país (Colombia, USA, y opcionalmente MEX/ARG/ESP) se mide en 4 dimensiones:

| Dimensión | Fuente | Métrica primaria |
|---|---|---|
| **Web** | GSC `search type=web` + DataForSEO `serp/google/organic` | Posición promedio ponderada por impresiones del cluster comercial |
| **Imágenes** | GSC `search type=image` + DataForSEO `serp/google/images` | Posición promedio ponderada + share de imágenes del sitio en top 20 |
| **Video** | GSC `search type=video` + DataForSEO SERP con feature `video` | Impresiones/mes de videos indexados + apariciones en carrusel |
| **Shopping** | DataForSEO SERP con feature `popular_products` | Presencia de productos del sitio en el bloque |

Bonus: **AI presence** — matriz `queries × LLMs` con score de mención. Se mide con `ai_optimization/llm_mentions/*` y `ai_optimization/{llm}/llm_responses`.

## Model routing (Julian Goldie idea, adoptada)

Cada tipo de trabajo va a un modelo distinto por costo/velocidad:

| Trabajo | Modelo | Racional |
|---|---|---|
| Outline de pillar page, análisis competitivo, decisiones estratégicas, revisión de drafts críticos | **Opus** | Bajo volumen, alto valor, requiere pensamiento profundo |
| Draft inicial de posts intermedios, adaptación de contenido a nichos, síntesis de PAA | **Sonnet** | Balance costo/calidad |
| Alt-text batch (300+ imágenes), meta descriptions, FAQ generation, video descriptions YouTube, schema markup, snippets 155 caracteres | **Haiku** | Alto volumen, patrón repetitivo, formato acotado |

Los prompts en `prompts/` declaran su modelo objetivo en frontmatter.

## Cadencia

- **Semanal:** snapshot de posiciones (`workflows/06_monitor.md`).
- **Quincenal:** publicar 1-2 piezas nuevas.
- **Mensual:** revisión de content plan, ajuste de prioridades según gaps que cerraron / nuevos que aparecieron.
- **Trimestral:** rebench de presencia en LLMs, comparar t0 vs t+3m vs t+6m.

## Ciclo de aprobaciones

- **Autónomo** (no requiere OK humano): investigación (DataForSEO), análisis, drafts en Markdown, exportar snapshots.
- **Requiere OK antes de ejecutar** (POLICIES.md los enumera): publicar contenido, modificar meta/schema de páginas existentes, cambios de URL, submit a Indexing API, outreach real a otros sitios, cerrar contenido de bajo rendimiento.
