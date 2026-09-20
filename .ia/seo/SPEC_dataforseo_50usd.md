> **⚠️ SUPERSEDED** — este SPEC fue reemplazado el 2026-09-20 por `specs/active/rankear-2026/` que reencuadra la meta hacia **rankear** (por país + categoría) y **plan de contenidos asistido por LLM**, no hacia investigación técnica pura. Se mantiene aquí como referencia del gasto de $50 USD y como fuente de las misiones técnicas que quedaron absorbidas en la Fase 1 del nuevo proposal.

---

# SPEC — Plan DataForSEO $50 USD para Maquiempanadas (SUPERSEDED)

**Fecha:** 2026-09-20
**Autor:** Nicolás Navarro (MySEO) + Claude
**Estado:** propuesta pendiente de ejecución
**Presupuesto:** $50 USD comprado en DataForSEO (cuenta verificada, $0.38 USD ya consumidos en batch inicial de septiembre)
**Balance disponible:** ~$49.60 USD
**Objetivo:** transformar el crédito en decisiones concretas de SEO para Maquiempanadas, no en consultas exploratorias.

---

## Contexto

Maquiempanadas ya tiene diagnóstico SEO consolidado (ver `analisis_keywords_gsc.md` y `reporte_maquiempanadas.html`):
- 18 keywords en posición #1 en Google USA (español).
- Compite en el mercado hispano contra `adlovermaquinas.com` (5 pos 1 en COL); en el mercado anglófono industrial contra `ankofood.com` (145 kw rankeando); en Ads contra `ferrero-machines.com`.
- Tiene brecha técnica en imágenes (posición promedio 21-36) y en video (33 impresiones/16m con 37 videos con incidencia de indexación).
- Aparece en respuestas de IA generativa (15,253 imp/3m, tendencia creciente).

**Lo que DataForSEO puede aportar sobre este diagnóstico:** información que GSC + Ads no dan — volumen de mercado no dependiente del sitio, keywords de rivales, auditoría técnica del sitio, perfil de backlinks, presencia en LLMs.

---

## Objetivos concretos

Al terminar el plan, deben existir:

1. **Un mapa expandido de 300-500 keywords** con volumen mensual, dificultad e intent clasificado, para USA (español + inglés) y Colombia.
2. **Una lista de content gaps** — keywords donde `ankofood.com`, `adlovermaquinas.com` y `ferrero-machines.com` rankean top 3 y Maquiempanadas no aparece en top 20.
3. **Una lista priorizada de fixes técnicos on-page** (schemas rotos, 404, redirects, meta duplicados, page speed) para maquiempanadas.com.
4. **Un plan de outreach para backlinks** basado en dónde consiguen enlaces los rivales.
5. **Un diagnóstico de posicionamiento en LLMs** (ChatGPT, Claude, Gemini, Perplexity) — ¿mencionan a Maquiempanadas cuando la gente pregunta por máquinas de empanadas?
6. **Monitoreo mensual configurado** sobre 30-50 keywords principales para detectar cambios de posición.

Cada objetivo debe traducirse en al menos una acción ejecutable sobre el sitio (código, contenido, outreach) — si no, no vale gastar el crédito.

---

## Alcance

**Mercados:** USA (español + inglés) y Colombia como benchmark. España queda fuera de este spec.

**Dominios en análisis:** `maquiempanadas.com` como principal. Rivales: `ankofood.com`, `anko.com.tw`, `ferrero-machines.com`, `adlovermaquinas.com`.

**Idiomas:** español e inglés en USA; español en Colombia.

**Fuera de alcance:**
- Análisis para otros clientes de MySEO.
- Implementación de los fixes técnicos (este spec produce la lista, no ejecuta el código).
- Creación de contenido para los content gaps (este spec identifica gaps, no escribe posts).
- Automatización del monitoreo en dashboard interno (se propone script simple, no BI).

---

## Misión 1 — Expandir el universo de keywords

**Objetivo:** pasar de ~30 keywords semilla a 300-500 con volumen, dificultad e intent.

**Endpoints y costos aproximados:**

| Endpoint | Uso | Costo estimado |
|---|---|---:|
| `dataforseo_labs/google/keyword_ideas/live` | Ideas de keywords desde 5 semillas | $1.00 |
| `dataforseo_labs/google/keyword_suggestions/live` | Variantes long-tail (autocomplete-like) | $0.50 |
| `dataforseo_labs/google/related_keywords/live` | Keywords semánticamente relacionadas | $0.50 |
| `dataforseo_labs/google/search_intent/live` | Clasifica intent de 100-200 keywords | $2.00 |
| `dataforseo_labs/google/bulk_keyword_difficulty/live` | Dificultad por batch | $1.00 |

**Semillas para arrancar:**
- Español: `maquina para hacer empanadas`, `maquina para hacer arepas`, `molde para empanadas`, `desmechadora`, `laminadora`
- Inglés: `empanada machine`, `arepa machine`, `empanada maker`, `dough press`, `masa machine`

**Entregable:** CSV con columnas `keyword`, `volume_usa`, `volume_col`, `cpc`, `competition`, `keyword_difficulty`, `intent`, `parent_topic`.

**Costo estimado misión 1:** ~$5 USD.

---

## Misión 2 — Rankings de rivales y content gaps

**Objetivo:** identificar qué keywords capturan los rivales que Maquiempanadas ni siquiera tiene en el radar.

**Endpoints y costos aproximados:**

| Endpoint | Uso | Costo estimado |
|---|---|---:|
| `dataforseo_labs/google/ranked_keywords/live` | Top 100 keywords donde rankea cada rival (4 dominios × 2 países = 8 llamadas) | $1.20 |
| `dataforseo_labs/google/domain_intersection/live` | Keywords donde compiten Maqui vs cada rival (3 rivales × 2 países = 6 llamadas) | $0.60 |
| `dataforseo_labs/google/serp_competitors/live` | Competidores agregados para el set de keywords de misión 1 | $0.30 |
| `dataforseo_labs/google/page_intersection/live` | Páginas específicas que compiten | $0.30 |

**Entregable:** CSV con columnas `keyword`, `volume`, `pos_maqui`, `pos_anko`, `pos_ferrero`, `pos_adlover`, `page_maqui_url`, `page_rival_url`, `gap_type` (gap total / gap de posición / donde Maqui gana).

**Costo estimado misión 2:** ~$3 USD.

---

## Misión 3 — Auditoría técnica on-page

**Objetivo:** encontrar errores técnicos del sitio (schemas rotos, 404, redirects, meta duplicados, page speed, imágenes sin optimizar).

**Endpoints y costos aproximados:**

| Endpoint | Uso | Costo estimado |
|---|---|---:|
| `on_page/task_post` + `on_page/summary` | Crawl completo de maquiempanadas.com (limit 500 URLs) | $3.00 |
| `on_page/duplicate_content` | Contenido duplicado entre URLs | $0.50 |
| `on_page/redirect_chains` | Cadenas de redirect | $0.50 |
| `on_page/broken_resources` | Recursos rotos (imágenes, JS, CSS) | $0.50 |
| `on_page/links` | Estructura interna de enlaces | $0.50 |
| `on_page/instant_pages` | Auditoría individual de 10 páginas clave | $2.00 |

**Páginas clave para instant_pages:**
- Home `/` (ES + EN)
- CM05S, CM06, CM06B, CM07, CM08
- Categoría moldes, categoría arepas
- Top 3 blog posts

**Entregable:** lista con `issue_type`, `severity` (crítico/medio/bajo), `url_afectada`, `impacto_seo_estimado`, `fix_sugerido`.

**Costo estimado misión 3:** ~$8 USD.

---

## Misión 4 — Perfil de backlinks + comparación con rivales

**Objetivo:** entender el perfil de enlaces actual de Maquiempanadas y qué oportunidades usan los rivales.

**Endpoints y costos aproximados:**

| Endpoint | Uso | Costo estimado |
|---|---|---:|
| `backlinks/summary/live` | Perfil resumen del dominio | $0.10 |
| `backlinks/domain_pages/live` | Qué páginas atraen más enlaces | $0.30 |
| `backlinks/anchors/live` | Distribución de anchor text | $0.30 |
| `backlinks/backlinks/live` | Lista de backlinks (limit 200) | $2.00 |
| `backlinks/competitors/live` | Comparar perfil con rivales | $1.00 |

Se ejecuta el mismo set para 3 rivales: `ankofood.com`, `ferrero-machines.com`, `adlovermaquinas.com` — para comparación.

**Entregable:** CSV con `dominio_referente`, `dr` (domain rank), `enlaza_a` (Maqui/Anko/Ferrero/Adlover), `anchor_text`, `tipo` (dofollow/nofollow), `oportunidad_outreach` (sí/no + razón).

**Costo estimado misión 4:** ~$5 USD (con rivales).

---

## Misión 5 — Presencia en LLMs (AEO/GEO)

**Objetivo:** medir si Maquiempanadas es citada por ChatGPT, Claude, Gemini y Perplexity cuando alguien busca información sobre máquinas de empanadas.

**Endpoints y costos aproximados:**

| Endpoint | Uso | Costo estimado |
|---|---|---:|
| `ai_optimization/llm_mentions/target_metrics/live` | Métricas de menciones para maquiempanadas.com | $0.50 |
| `ai_optimization/llm_mentions/top_mentioned_domains/live` | Qué dominios cita cada LLM para queries del sector | $1.00 |
| `ai_optimization/llm_mentions/top_mentioned_brands/live` | Qué marcas cita cada LLM | $0.50 |
| `ai_optimization/{chat_gpt\|claude\|gemini\|perplexity}/llm_responses/live` | 10 queries clave × 4 LLMs = 40 llamadas | $2.00 |
| `ai_optimization/llm_mentions/historical/live` | Historial de menciones | $0.50 |

**Queries a lanzar a los 4 LLMs:**
- "cuál es la mejor máquina para hacer empanadas"
- "best empanada machine for a restaurant"
- "empanada making machine price"
- "cómo empezar un negocio de empanadas"
- "máquina para hacer arepas colombianas"
- "empanada maker vs empanada press"
- "recomendaciones de máquinas para producir empanadas industrialmente"
- "comparar Maquiempanadas Ferrero Anko"
- "who makes commercial empanada machines"
- "dónde comprar máquina para empanadas"

**Entregable:** matriz `query × llm` con score de mención de Maquiempanadas + top 3 dominios/marcas citadas por LLM.

**Costo estimado misión 5:** ~$5 USD.

---

## Misión 6 — Setup de monitoreo mensual

**Objetivo:** después de aplicar cambios, monitorear si mejoran las posiciones.

**Endpoints y costos aproximados:**

| Endpoint | Uso | Costo estimado mensual |
|---|---|---:|
| `serp/google/organic/live/advanced` | SERP tracking de 30 keywords principales × 2 países = 60 llamadas | $0.24/mes |
| `dataforseo_labs/google/historical_serps/live` | Historial SERP por keyword crítica | $0.30/mes |
| `dataforseo_labs/google/historical_rank_overview/live` | Rank overview mensual del dominio + 3 rivales | $0.05/mes |

**Entregable:** script Python programado (cron) que:
1. Ejecuta las llamadas
2. Guarda resultados en `.ia/seo/monitoring/YYYY-MM/`
3. Genera un CSV `changes.csv` con `keyword`, `pos_anterior`, `pos_actual`, `delta`

**Costo estimado misión 6:** ~$1 USD/mes × 6 meses = $6 USD reservado.

---

## Presupuesto total y reserva

| Misión | Costo estimado | Acumulado |
|---|---:|---:|
| 1 — Expansión keywords | $5 | $5 |
| 2 — Rankings rivales | $3 | $8 |
| 3 — Auditoría técnica | $8 | $16 |
| 4 — Backlinks | $5 | $21 |
| 5 — LLMs | $5 | $26 |
| 6 — Monitoreo (6 meses reservados) | $6 | $32 |
| **Reserva para iteraciones** | **$18** | **$50** |

**Reserva de $18 USD** — casi siempre algún batch revela algo que hay que profundizar (una keyword que resulta más grande, un competidor nuevo, un fix técnico que abre otro).

---

## Criterios de aceptación

El plan se considera cumplido cuando:

- [ ] Existe `keywords_expanded.csv` con ≥300 filas y columnas requeridas (misión 1).
- [ ] Existe `content_gaps.csv` con ≥50 gaps identificados (misión 2).
- [ ] Existe `technical_audit.md` con lista priorizada de issues (misión 3).
- [ ] Existe `backlinks_opportunities.csv` con ≥50 dominios prospecto (misión 4).
- [ ] Existe `llm_presence_matrix.md` con la matriz 10 queries × 4 LLMs (misión 5).
- [ ] Existe `monitoring/` con script + primer run ejecutado (misión 6).
- [ ] Cada CSV/MD lleva metadata: fecha, comando exacto ejecutado, versión de datos.
- [ ] El gasto real reportado por DataForSEO no supera **$40 USD** (deja $10 de margen sobre el estimado).

---

## Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Los volúmenes de DataForSEO no coinciden con los de Google Ads (fuente subyacente puede diferir por muestreo) | Comparar con muestras del propio Ads search terms report ya disponible |
| La auditoría on-page se sobrepasa del límite de URLs y cobra más | Setear `max_crawl_pages: 500` explícitamente en la config del task |
| Anko/Ferrero cambian de estrategia mientras se ejecuta el análisis | Los datos son foto del momento — documentar fecha exacta de cada consulta |
| El endpoint de LLMs devuelve respuestas cambiantes (los LLMs son estocásticos) | Ejecutar cada query 2 veces y reportar concordancia |
| Se descubre que algún fix técnico requiere trabajo grande (ej. reescribir schema en todo el sitio) | Este spec produce la lista, no ejecuta — la implementación se planifica aparte |

---

## Orden de ejecución sugerido

1. **Misión 1** (keywords) — es insumo de misiones 2, 5 y 6.
2. **Misión 2** (rivales) — necesita el universo expandido de misión 1.
3. **Misión 3** (técnica) — independiente, puede ir en paralelo con 1-2.
4. **Misión 4** (backlinks) — independiente.
5. **Misión 5** (LLMs) — usa las keywords de misión 1.
6. **Misión 6** (monitoreo) — al final, cuando se sepa qué keywords vigilar.

Cada misión termina con un push a `main` del CSV/MD generado bajo `.ia/seo/dataforseo_output/` — así queda registro público del avance.

---

## Preguntas abiertas antes de lanzar

1. ¿Colombia entra como benchmark solo (misión 1 y 2) o también en monitoreo mensual (misión 6)?
2. ¿Los fixes técnicos de misión 3 los ejecuta el equipo de desarrollo de Maquiempanadas o MySEO?
3. ¿El outreach de backlinks (misión 4) tiene alguien asignado?
4. ¿Se aceptan las 10 queries propuestas para LLMs o se ajustan primero?
5. ¿El monitoreo mensual se envía a alguien por email o queda solo como archivo?

---

## Notas de implementación

- Cada llamada guarda su respuesta JSON cruda en `.ia/seo/data/dataforseo/YYYY-MM-DD_<endpoint>_<slug>.json`.
- El agregado (CSV/MD) se genera con scripts Python simples versionados en `.ia/seo/dataforseo_output/scripts/`.
- El SDK PHP oficial ya está descargado en `.ia/seo/dataforseo_xmpl_v3_php/` — se puede reutilizar para automatizar.
- Credenciales en `myseo-env/.env` (DATAFORSEO_BASE64_FORMAT).
- Precios de referencia: https://dataforseo.com/pricing (los costos aquí son estimados a septiembre 2026 y pueden variar).
