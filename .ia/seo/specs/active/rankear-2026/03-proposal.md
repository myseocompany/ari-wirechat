---
slug: rankear-2026
proposed_at: 2026-09-20
---

# Proposal — Rankear 2026

## Resumen

Rankear top 3 en 4 categorías universales de Google (texto / imágenes / video / shopping) atacando **6 nichos culturalmente específicos** definidos por la cliente + 2 mercados profesionales cruzados. Los nichos son intersecciones audiencia × geografía × producto identitario × vocabulario local: cubanos en USA (empanada cubana), puertorriqueños (empanadilla/pastelillo), costarricenses (chiverre/queso), chilenos (empanada de pino), venezolanos (harina de maíz + arepas) y colombianos en España.

Estrategia de **dos pilares de producto**: CM06 (empanadas + arepas, puerta de entrada) para buyer inicial + CM5S (multifuncional con telemetría, competencia directa vs Anko) para buyer profesional. Cada nicho recibe una landing específica que enlaza al pilar correspondiente según intención comercial.

Combinar investigación DataForSEO (presupuesto $45 USD ya comprados, distribuidos en 8 batches por `location_code`), benchmark cross-category (100+ SERPs de nichos B2B análogos), y motor de generación de contenidos asistido por LLM con revisión humana obligatoria + activos originales de Maquiempanadas + autor identificado, para publicar **15 piezas nicho-específicas** en 12 semanas.

**El feature se ejecuta bajo el SEO Harness** (`.ia/seo/harness/`) que define SOUL, MÉTODO (RESEARCH → GAP_ANALYSIS → CONTENT_PLAN → GENERATE → PUBLISH → MONITOR → EVOLVE), POLÍTICAS, skills (content QC con 13 checkpoints, model routing Opus/Sonnet/Haiku, data hygiene), workflows y prompts. El harness es la contraparte MYSEO de SPECBOOT (que queda para el código Laravel del repo).

## Cambios propuestos

### Fase 0 — Setup y baseline (semanas 1-2)

Trabajo de infraestructura. No cambia el sitio todavía.

- `.ia/seo/scripts/dataforseo_client.py` — cliente Python que envuelve las llamadas a DataForSEO, guarda JSON crudo con timestamp, escribe fila de costo en `COSTS.csv`.
- `.ia/seo/scripts/serp_snapshot.py` — captura SERP + guarda para comparación t0 vs t+N.
- `.ia/seo/scripts/llm_prompts/` — biblioteca de prompts de generación por tipo de contenido (pillar page, cluster post, product page, FAQ, alt-text batch).
- `.ia/seo/data/dataforseo/COSTS.csv` — control de gasto contra el techo $45.
- `.ia/seo/data/dataforseo/keywords_universe.csv` — inventario final tras fase 1 de investigación.
- `.ia/seo/data/dataforseo/content_gaps.csv` — gaps priorizados por (volumen × posición_rival) tras fase 2.
- `.ia/seo/data/dataforseo/benchmark_categories.csv` — resultados del recorrido cross-category (fase 3).
- `.ia/seo/data/dataforseo/llm_presence_baseline.md` — baseline t0.

### Fase 1 — Investigación keywords y gaps por nicho (semanas 2-3)

Consultas DataForSEO priorizadas. **Presupuesto de esta fase: $16 USD** (subió $4 vs versión anterior por los 6 nichos).

**Location codes por nicho:**

| Nicho | `location_code` | `language_code` | Notas |
|---|---:|---|---|
| N1 Cubanos en USA | 2840 | es | USA español general — la diáspora cubana busca en `.com/es` |
| N2 Puertorriqueños | 2630 (Puerto Rico) + 2840 | es | PR tiene código propio; complementar con USA |
| N3 Costarricenses | 2188 | es | Google Costa Rica |
| N4 Chilenos | 2152 | es | Google Chile |
| N5 Venezolanos | 2862 | es | Google Venezuela |
| N6 Colombianos en España | 2724 | es | Google España |
| P1 Anglófono industrial | 2840 | en | USA inglés |
| P2 Profesional hispano | 2170 (COL) + 2840 + 2152 + 2724 | es | benchmark de comparación entre países |

**Batch 1.1 — Expansión de universo semántico por nicho ($6 USD):**
- `dataforseo_labs/google/keyword_ideas/live` — semillas culturales específicas por nicho:
  - N1: `empanada cubana`, `pastelito guayaba y queso`, `empanada de guayaba`
  - N2: `empanadilla`, `pastelillo`, `pastelillo de carne`
  - N3: `empanada tica`, `empanada de chiverre`, `empanada de queso costa rica`
  - N4: `empanada chilena`, `empanada de pino`, `empanada frita chilena`
  - N5: `empanada venezolana`, `harina PAN empanadas`, `empanada de queso venezolana`, `arepa`
  - N6: `empanadas colombianas España`, `empanadas colombianas Madrid`
  - P1: `empanada machine`, `commercial empanada machine`, `automatic empanada maker`
  - P2: `máquina industrial empanadas`, `línea producción empanadas semiautomática`
- `dataforseo_labs/google/keyword_suggestions/live` — variantes long-tail sobre las top 30 por nicho.
- `dataforseo_labs/google/related_keywords/live` — semánticamente relacionadas.
- `dataforseo_labs/google/search_intent/live` — clasifica intent de las top 200.

**Batch 1.2 — Volumen y dificultad por país específico ($5 USD):**
- `keywords_data/google_ads/search_volume/live` con el `location_code` correcto de cada nicho para las 30-50 keywords semilla de cada uno. 8 batches × 30-50 kw.
- `dataforseo_labs/google/bulk_keyword_difficulty/live` por batch por nicho.
- **Salida clave:** volumen por nicho para priorizar. Probablemente Chile >> Costa Rica en volumen bruto; hay que confirmar con datos.

**Batch 1.3 — Rivales por nicho y gaps ($5 USD):**
- `dataforseo_labs/google/ranked_keywords/live` para 5 dominios (ankofood, anko.com.tw, ferrero-machines, adlovermaquinas, empanadasmachine) × países prioritarios (USA + COL + los 4 nuevos donde tengan presencia) — hasta 10 requests.
- Rivales locales por nicho (a identificar en Fase 2 SERPs): para Chile, buscar `metalurgicavazquez.com.ar` o similares; para España, buscar competidores locales de maquinaria alimentaria.
- `dataforseo_labs/google/domain_intersection/live` — Maqui vs cada rival principal.
- `dataforseo_labs/google/serp_competitors/live` — competidores agregados por keyword set nicho-específico.

**Salida:** `keywords_universe.csv` con 500+ keywords **clasificadas por nicho** + `content_gaps.csv` con ≥100 gaps priorizados. Sin escribir nada aún.

### Fase 2 — Benchmark cross-category (semanas 3-4)

Recorrer SERPs de nichos B2B con productos físicos caros para aprender **qué formatos ganan**. No es investigación de nuestra keyword, es investigación de nuestro tipo de negocio.

**Nichos a benchmarquear:**
- Máquinas de tortillas industriales (mercado latino directo adyacente)
- Máquinas de sushi / gyoza / dumpling (Anko compite ahí)
- Equipos de panadería industrial
- Extractoras de café comerciales
- Máquinas de helado industrial

**Presupuesto de esta fase: $8 USD.**

**Batch 2.1 — SERPs de referencia ($5 USD):**
- 10 keywords cabeza de cada nicho × 5 nichos × 2 países (USA + COL) = 100 SERPs
- `serp/google/organic/live/advanced` con `depth=30`

**Batch 2.2 — Análisis de las top URLs ganadoras ($3 USD):**
- Top 3 URL por cada SERP recorrido = 300 URLs
- `on_page/instant_pages` para 30 páginas seleccionadas (las más ricas en features)

**Salida:** `benchmark_categories.md` con patrones ganadores:
- Formatos de pillar page (H1-H6, longitud, imágenes, video embed, FAQ, tabla de specs)
- Uso de structured data
- Estrategias de cluster (¿cuántos posts satelitales por producto?)
- Formatos que aparecen en AI Overviews del nicho
- Estilos de imagen que ganan en Google Images para producto industrial
- Formatos de video que rankean

**Este es el input creativo del content plan** — no se inventa qué escribir, se aprende de quien ya gana.

### Fase 3 — Content plan asistido por LLM (semanas 4-6)

**Presupuesto DataForSEO de esta fase: $10 USD.**

**Batch 3.1 — Auditoría del contenido actual ($5 USD):**
- `on_page/summary` + `on_page/duplicate_content` sobre `maquiempanadas.com` para detectar qué URLs actuales pueden expandirse (quick wins) vs. crear nuevas.
- `on_page/broken_resources` para detectar imágenes rotas / videos mal servidos.

**Batch 3.2 — Presencia en LLMs baseline ($5 USD):**
- `ai_optimization/{chat_gpt|claude|gemini|perplexity}/llm_responses/live` — 10 queries × 4 LLMs = 40 llamadas.
- `ai_optimization/llm_mentions/target_metrics/live` para maquiempanadas.com + rivales.

**Motor de generación de contenidos con LLM:**

Cada tipo de contenido tiene su prompt template en `.ia/seo/scripts/llm_prompts/`:

1. **`pillar_page.md`** — pillar por producto (CM05S, CM06, CM06B, CM07, CM08, laminadora, desmechadora):
   - Input: specs reales del producto + top 3 URL competidoras + PAA del SERP
   - Output: outline completo con secciones, tabla de specs, FAQ, structured data

2. **`cluster_post.md`** — post satélite alrededor de un pillar:
   - Input: gap del content_gaps.csv + pillar page relacionada + top 3 competidoras
   - Output: draft con intro, cuerpo por secciones, CTA a pillar

3. **`product_faq.md`** — FAQ para páginas de producto:
   - Input: 4 PAA del SERP + specs del producto
   - Output: 8-12 pares Q/A con schema FAQPage

4. **`alt_text_batch.md`** — alt-text bilingüe para imágenes existentes:
   - Input: URL de imagen + contexto de página
   - Output: alt-text descriptivo ES + EN

5. **`local_landing.md`** — landing por ciudad (Miami, Houston, Bogotá, Medellín, Manizales):
   - Input: ciudad + diáspora relevante + queries locales
   - Output: página con contenido geo-específico + LocalBusiness schema

6. **`video_description.md`** — descripciones de YouTube + títulos SEO:
   - Input: URL video + producto + keywords objetivo
   - Output: título, descripción, tags, timestamp chapters

**Cada draft pasa por 3 filtros antes de publicar:**
1. **Filtro humano** — Nicolás o editor revisa: veracidad, tono, links.
2. **Filtro E-E-A-T** — checklist: ¿hay autor identificado?, ¿fotos originales?, ¿video propio?, ¿testimonio verificable?.
3. **Filtro Rich Results Test** — el schema pasa validación de Google.

**Piezas a producir (15 en 12 semanas, distribuidas por 6 nichos + 2 mercados profesionales):**

### Pilares master (2)

| # | Tipo | Título tentativo | Idioma | Producto | Target keyword | Rol |
|---|---|---|---|---|---|---|
| 1 | Pillar master | Máquinas para hacer empanadas y arepas: guía completa | ES | **CM06** | `maquina para hacer empanadas y arepas` | Puerta de entrada — buyer inicial |
| 2 | Pillar master | Máquina profesional multifuncional para empanadas — CM5S con telemetría | ES | **CM5S** | `maquina industrial empanadas`, `linea produccion empanadas` | Comercial premium — buyer profesional |

### Landing pages nicho-específicas (6)

Una por audiencia. Cada una con vocabulario local, foto/video del producto identitario, testimonio de cliente real del nicho, LocalBusiness schema apuntando a la bodega que despacha (USA para N1/N2, COL para N3/N4/N5/N6, decidir por logística para N6). Todas enlazan al pilar CM06 o CM5S según intención comercial.

| # | Tipo | Nicho | Target keyword | Producto | Bodega despacha |
|---|---|---|---|---|---|
| 3 | Landing nicho | N1 Cubanos en USA | `maquina para hacer empanadas cubanas`, `maquina pastelitos guayaba y queso` | CM06 (arranque) + link a CM5S | USA |
| 4 | Landing nicho | N2 Puertorriqueños | `maquina para hacer empanadillas`, `maquina pastelillos puertorriqueños` | CM06 | USA |
| 5 | Landing nicho | N3 Costarricenses | `maquina para hacer empanadas costa rica`, `maquina empanadas de chiverre` | CM06 | Colombia |
| 6 | Landing nicho | N4 Chilenos | `maquina para hacer empanadas chilenas`, `maquina empanadas de pino` | CM06 + CM5S | Colombia |
| 7 | Landing nicho | N5 Venezolanos | `maquina para hacer empanadas venezolanas`, `maquina para arepas venezolanas` | CM06 (cluster fuerte de arepas) | Colombia |
| 8 | Landing nicho | N6 Colombianos en España | `maquina empanadas colombianas Madrid`, `venta maquina empanadas España` | CM5S (buyer profesional) | USA o COL según costo |

### Posts cluster por nicho (6)

Uno por nicho, tipo "cómo empezar negocio de [empanada del nicho] en [ciudad]". Enlazan a la landing nicho + al pilar correspondiente. Formato blog SEO informacional que refuerza el pilar.

| # | Tipo | Nicho | Título tentativo | Target keyword |
|---|---|---|---|---|
| 9 | Cluster | N1 | Cómo montar un negocio de pastelitos cubanos en Miami | `negocio empanadas cubanas Miami`, `franquicia pastelitos` |
| 10 | Cluster | N2 | Cómo hacer empanadillas puertorriqueñas para vender | `negocio empanadillas Puerto Rico`, `receta empanadilla masa` |
| 11 | Cluster | N3 | Cómo iniciar producción de empanadas ticas | `empanadas ticas para vender`, `negocio empanadas Costa Rica` |
| 12 | Cluster | N4 | Producción industrial de empanadas de pino en Chile | `empanadas chilenas al por mayor`, `fabrica empanadas Chile` |
| 13 | Cluster | N5 | Cómo producir empanadas de maíz venezolanas | `empanadas venezolanas negocio`, `harina PAN industrial` |
| 14 | Cluster | N6 | Empanadas colombianas para hostelería en España | `empanadas colombianas mayorista España`, `distribuidor empanadas Madrid` |

### Pilar en inglés (1)

| # | Tipo | Mercado | Título tentativo | Target keyword |
|---|---|---|---|---|
| 15 | Pillar EN | P1 Anglófono industrial USA | Commercial empanada machines: complete buying guide (CM5S) | `commercial empanada machine`, `automatic empanada maker machine` |

### Optimizaciones colaterales incluidas en el ciclo (no cuentan como "piezas" pero son entregables)

- **Product FAQ ampliada** para CM06 y CM5S existentes con las 4 PAA del SERP objetivo de cada uno.
- **Rewrite de descripciones + títulos SEO** de 10 videos YouTube del canal.
- **Auditoría y fix de alt-text** en imágenes existentes de páginas de producto (Haiku batch).
- **hreflang correcto** para las 6 landings nicho + `/en/` + `/es/` base.
- **Structured data adicional** (LocalBusiness apuntando a bodega, Product/Offer, VideoObject) en las páginas nuevas.

Las columnas de volumen y gap concretas se completan tras Fase 1. **Regla de asignación** post-Fase 1: si un nicho tiene volumen muy menor (ej. Costa Rica < 50 imp/mes en todas sus keywords), su landing se conserva pero el post cluster puede consolidarse con un nicho adyacente. Aprobación de la cliente requerida antes de reasignar.

### Fase 4 — Publicación técnica y monitoreo (semanas 6-24)

**Presupuesto DataForSEO de esta fase: $10 USD ($5 para monitoreo × 2 primeros meses; luego se factura mensual).**

**Batch 4.1 — Publicación (sin DataForSEO):**
- Merge cada pieza publicada con: schema válido, alt-text, structured data, canonical, hreflang correcto.
- Video schema fix para los 37 videos con incidencia (fase de diagnóstico caso por caso, no fix ciego).

**Batch 4.2 — Monitoreo mensual ($3 USD/mes):**
- `serp/google/organic/live/advanced` de 30 keywords principales × 2 países = 60 requests/mes ≈ $0.24
- `serp/google/images/live/advanced` de 10 keywords principales × 2 países = 20 requests/mes ≈ $0.10
- `dataforseo_labs/google/historical_rank_overview/live` mensual del dominio + rivales ≈ $0.05
- Total: bien por debajo de $3/mes; se reserva margen.

**Batch 4.3 — Rebench de presencia en LLMs a t+3m y t+6m ($2 USD c/u):**
- Mismas 10 queries × 4 LLMs para comparar contra baseline.

### Distribución del presupuesto $45 USD (actualizado revisión 2)

| Fase | Costo | Acumulado |
|---|---:|---:|
| Fase 1 — Investigación por nicho (8 keyword sets) | **$16** | $16 |
| Fase 2 — Benchmark cross-category | $8 | $24 |
| Fase 3 — Auditoría + LLM baseline | $10 | $34 |
| Fase 4 — Monitoreo 3 meses + 2 rebenches LLM | $9 | $43 |
| Reserva iteraciones | $2 | $45 |

Fase 1 subió $4 vs versión anterior porque los 6 nichos requieren 8 batches de `search_volume` (uno por `location_code`) en lugar de 3 (USA es, USA en, COL). La reserva bajó de $6 a $2 — más ajustado. Si en Fase 1 se detecta que algún nicho tiene volumen despreciable, se puede recortar su investigación y liberar $1-2 para reserva.

## Contratos afectados

- **`maquiempanadas.com` HTML/JSON-LD:** cada URL nueva agrega structured data (Product, Video, FAQPage, HowTo, LocalBusiness según aplique). Los cambios son aditivos, no rompen contratos existentes.

- **YouTube channel:** descripciones y títulos de 10+ videos se reescriben para SEO. Contrato con la audiencia YouTube no se rompe — el contenido del video no cambia.

- **Google Search Console:** no hay contrato que romper, es unidireccional.

## Alternativas descartadas

- **Alternativa A: sólo SEO técnico + esperar que rankeen las páginas actuales.** Descartada porque el sitio ya tiene 18 kw en pos 1 y las páginas actuales no cubren gaps donde rivales rankean top 10. Sin contenido nuevo, el techo está cerca.

- **Alternativa B: enfoque solo en mercado anglófono industrial.** Descartada porque el SERP de `empanada maker` es dominado por consumidor doméstico (Amazon, restaurantes) y `empanada machine` por Anko con recursos difíciles de disputar. El mercado hispano es defendible y menos saturado.

- **Alternativa C: traducir el blog existente al inglés.** Descartada porque el 81% del tráfico USA actual es en español (diáspora latina) — traducir al inglés no capta ese comprador. Contenido en inglés se hace nuevo y original si hay gap real.

- **Alternativa D: generar todo el contenido con LLM sin revisión humana.** Descartada por E-E-A-T y por el riesgo de inventar specs de productos.

- **Alternativa E: contratar redactores externos sin usar LLM.** Descartada por velocidad. LLM acelera el draft; humano firma y corrige.

- **Alternativa F: seguir el SPEC anterior (`SPEC_dataforseo_50usd.md`) que priorizaba investigación técnica sobre contenido.** Descartada — su meta era "producir listas y CSVs", no "rankear". Esta propuesta absorbe sus mejores partes (investigación de gaps, backlinks) y las subordina al objetivo de contenido publicado.

## Plan de tests

Estos "tests" son de resultado SEO, no unit tests de código.

- **Verificación semanal automatizada:**
  - Script Python en cron corre `serp/google/organic/live/advanced` sobre keywords del `content_gaps.csv` marcadas como "en curso".
  - Genera diff vs semana anterior en `.ia/seo/monitoring/YYYY-WW/diff.md`.

- **Verificación por AC:**
  - **AC1** (universo keywords ≥500) → contar filas de `keywords_universe.csv` tras fase 1. Deadline: semana 3.
  - **AC2** (≥100 gaps) → contar filas de `content_gaps.csv`. Deadline: semana 3.
  - **AC3** (10 kw en top 3) → medir a t+6m con `dataforseo_labs/google/historical_serps/live` contra baseline t0.
  - **AC4** (imágenes pos ≤10 USA, ≤6 COL) → GSC image + `serp/google/images/live/advanced` a t+3m y t+6m.
  - **AC5** (video ≥1,000 impr/3m) → GSC video a t+3m y t+6m.
  - **AC6** (3 kw con producto en Popular products) → `serp/google/organic/live/advanced` a t+3m y t+6m.
  - **AC7** (15+ piezas publicadas) → checkeo manual + inventario en `.ia/seo/content_ledger.md`.
  - **AC8** (video schema válido) → Rich Results Test manual + reporte GSC Video Indexing.
  - **AC9** (LLM presence baseline + rebench) → `llm_presence_baseline.md`, `llm_presence_t3m.md`, `llm_presence_t6m.md`.
  - **AC10** (rastro JSON) → `.ia/seo/data/dataforseo/` + `COSTS.csv`.
  - **AC11** (presupuesto ≤$45) → sumatoria de `COSTS.csv` tras fase 4.
  - **AC12** (cada pieza referencia un gap) → checklist manual antes de publicar.

- **Verificación manual de calidad de contenido:**
  - Cada draft pasa por Nicolás antes de publicar.
  - Filtro E-E-A-T: ¿autor identificado?, ¿fotos originales?, ¿claim verificable?
  - Filtro Rich Results Test: schema pasa validación.
  - Filtro anti-inventos: cualquier claim numérico (precio, capacidad, dimensiones) validado contra ficha real del producto.

## Impacto operativo

- **Cambios en el sitio:** aditivos. Se agregan URLs nuevas + se enriquecen algunas existentes con schema y contenido adicional. No hay migraciones ni cambios de URL structure en este SPEC.

- **Feature flags:** no aplica — es contenido web público, no funcionalidad de app.

- **Rollback plan:** cada pieza publicada tiene rama Git antes del merge. Si Google penaliza (evidencia clara en GSC), se despublica la pieza específica y se investiga.

- **Datos existentes:** las 18 keywords en posición #1 actuales son intocables. Cualquier cambio en páginas que soporten esas keywords requiere validación previa.

- **Documentación:**
  - `.ia/seo/content_ledger.md` — inventario de piezas publicadas con fecha, target keyword, autor, URL.
  - `.ia/seo/monitoring/` — snapshots semanales de posiciones.
  - `analisis_keywords_gsc.md` se mantiene como diagnóstico histórico. Este SPEC no lo modifica.
  - `reporte_maquiempanadas.html` (cliente) se actualiza a t+3m y t+6m con resultados.

- **Tenants:** solo Maquiempanadas. No hay riesgo cruzado.

## Estimación

- **Complejidad:** media-alta. La investigación es directa; la creación de contenido de calidad E-E-A-T y su implementación técnica sostenida durante 6 meses es lo pesado.

- **Tiempo estimado LLM (fases 1-3):** ~40 horas de trabajo agéntico distribuidas en 6 semanas.

- **Tiempo humano requerido:** ~2-4 horas/semana de Nicolás para revisión + input de Maquiempanadas para activos originales (fotos, videos, autor).

- **Riesgo de regresión:** medio. Áreas específicas:
  - **Cambios de schema en páginas de producto existentes** — riesgo alto, requieren staging.
  - **Cambios en URLs de blog** — si se reestructuran, riesgo alto.
  - **Contenido nuevo bajo URLs nuevas** — riesgo bajo, es aditivo.

## Cambios respecto al plan original

*(Se rellena durante `/apply` si la realidad diverge.)*

## Adiciones del ciclo de refinado (post video-Julian-Goldie)

Cinco ideas concretas incorporadas al plan tras revisar el análisis del harness DeepSeek de Julian Goldie. Todas las que están adaptadas al contexto de Maquiempanadas (nada de auto-publishing ni auto-outreach que aquí romperían E-E-A-T).

### A. Skill file explícito con 13 checkpoints de QC

Antes definí "3 filtros" (humano, E-E-A-T, Rich Results Test). Se materializa como checklist ejecutable de 13 puntos en `.ia/seo/harness/skills/SKILL_content_qc.md`. Cada pieza publicada evidencia el cumplimiento de los 13 en su `trajectory.md`. Si algún punto falla → no se publica.

Los 13 puntos:

**Contenido — veracidad y E-E-A-T:**
1. Autor identificado con nombre + cargo real.
2. Sin invenciones numéricas (todo claim numérico verificable contra ficha oficial).
3. Foto o video original de Maquiempanadas (no stock).
4. Claims verificables con fuente o testimonio citable.

**Estructura — para AI Overviews y humanos:**
5. Respuesta directa al inicio (≤50 palabras).
6. Entidades reconocibles (modelos, marca, ciudad, categoría).
7. Datos concretos en primeras 500 palabras (≥3 con unidad).

**Estructura — SEO técnico:**
8. Structured data válido (Rich Results Test aprobado).
9. Alt-text en todas las imágenes.
10. Meta title ≤60c y description ≤155c, no duplicados.

**Interlinking:**
11. Enlace interno a pilar + producto.
12. Sin enlaces externos rotos.

**Trazabilidad:**
13. Trajectory log completo.

### B. Model routing consciente (Opus / Sonnet / Haiku)

Antes hablé de "generación con LLM" sin especificar modelo. Ahora explícito en `.ia/seo/harness/skills/SKILL_model_routing.md`:

| Trabajo | Modelo | Racional |
|---|---|---|
| Outline de pillar, análisis competitivo, revisión final | Opus | Alto valor, pocas piezas |
| Draft de posts, síntesis, adaptación a nichos | Sonnet | Balance |
| Alt-text batch, meta descriptions, FAQ items, schema markup, snippets | Haiku | Volumen alto, formato acotado |

Cada uso queda registrado en el `trajectory.md` con modelo, tokens, costo. Permite auditar economía real de generación en el tiempo.

### C. Impressions-without-clicks como fuente barata de gaps

Nuevo paso en Fase 4 (workflow `07_evolve.md`). Mensualmente:

- Filtrar `data/gsc/**/Consultas.csv` con `impressions > 500 AND CTR < 2% AND pos entre 4-10`.
- Estas queries están rankeando pero el snippet pierde el clic.
- Solución barata: reescribir title + meta description + agregar structured data si falta.
- Registrar como pieza tipo `snippet_optimization` en `content_ledger.md`.

Ejemplo actual: `maquina para hacer empanadas` USA — 2,783 impresiones, pos 5.24, CTR 2.84%. Clásico caso donde el título/meta pierde el clic. Corregirlo puede subir CTR a 5-7% sin cambiar posición — es decir, duplicar clics de la keyword head sin escribir un post nuevo.

Costo DataForSEO adicional: **$0** — todo es análisis sobre GSC.

### D. Google Indexing API push tras publicar

Nueva acción en workflow `05_publish.md` paso 3. Cada URL nueva se somete al Indexing API de Google para acelerar time-to-index de días/semanas a minutos.

- Endpoint: `https://indexing.googleapis.com/v3/urlNotifications:publish` con payload `{"url": "<url>", "type": "URL_UPDATED"}`.
- Requisito operativo: Service Account con permiso Owner en Search Console.
- Alternativa manual: "Request Indexing" desde GSC UI.
- La respuesta del API se registra en el `trajectory.md` de la pieza (paso 7).

**Requiere aprobación humana explícita** por rate limits de Google — no se hace automáticamente.

### E. Trajectory log obligatorio por pieza

Ya estaba implícito en la sección "Verificación por AC" pero ahora es un artefacto de primera clase: `.ia/seo/harness/templates/TRAJECTORY.md` es la plantilla.

Cada pieza publicada tiene su carpeta `content/YYYY-MM-DD_<slug>/` con:
- `trajectory.md` — motivación (gap), SERP estudiado, benchmark análogo consultado, drafts + modelos LLM, checklist QC, revisión humana, publicación, seguimiento t+30/90/180d.
- `draft.md` — el contenido final antes de publicar.
- `APPROVAL.md` (si aplica) — solicitud de aprobación humana firmada.

Sin `trajectory.md` completo, la pieza no cuenta contra los AC del feature. Permite:
- Auditar retroactivamente por qué se publicó cada cosa.
- Reproducir el análisis si se pierde el rastro.
- Aprender cuáles ángulos ganan (patrón entre trajectories exitosos vs. flops).

### Impacto agregado en presupuesto

Las 5 adiciones **no aumentan el techo de $45 USD** del feature:
- A y E son cambios organizativos (más disciplina, no más gasto).
- B redistribuye el gasto LLM entre modelos — probablemente ahorra por usar Haiku donde antes usaría Sonnet por default.
- C es análisis sobre datos GSC que ya tenemos.
- D es API gratis de Google.

## Notas sobre mejores prácticas SEO 2026 integradas

Este plan incorpora explícitamente:

1. **AI Overviews como objetivo primario** — no solo rankear top 3 orgánico, sino ser citado en la respuesta de IA. Formato de contenido: respuesta directa al inicio, datos concretos, entidades reconocibles.

2. **Presencia en LLMs (AEO / GEO)** — ChatGPT, Claude, Gemini, Perplexity como canales de descubrimiento medibles. Sección 5 dedicada.

3. **E-E-A-T reforzado** — Google 2026 exige demostración de experiencia, no solo declaración. Autor real + activos originales obligatorios.

4. **Multi-modal content** — texto + imagen + video en cada pieza clave, no piezas solo-texto.

5. **Structured data expandido** — Product, Video, FAQ, HowTo, LocalBusiness. Cada pieza pasa Rich Results Test.

6. **Content clusters con pillar pages** — no posts sueltos. Pillar por producto + satélites que se refuerzan mutuamente.

7. **Local SEO para diáspora** — landing pages por ciudad para USA (Miami, Houston) + Colombia (Bogotá, Medellín). LocalBusiness schema.

8. **Video-first en 2026** — reconectar el canal YouTube (1.18M views/año) al sitio mediante embed + schema correcto + páginas dedicadas.

9. **Benchmark cross-category** — aprender de nichos B2B análogos (tortillas industriales, sushi/gyoza, panadería) qué formatos ganan. No inventar.

10. **Zero-click optimization** — snippets, PAA, AI Overview: contenido optimizado para "aparecer" no solo para "traer clic".
