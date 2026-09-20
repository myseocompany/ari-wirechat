---
slug: rankear-2026
proposed_at: 2026-09-20
---

# Proposal — Rankear 2026

## Resumen

Combinar investigación de mercado (DataForSEO, presupuesto $45 de los $50 comprados), benchmark cross-category (recorrer 100+ SERPs de nichos B2B con productos físicos caros para aprender patrones ganadores), y un motor de generación de contenidos asistido por LLM con revisión humana, para publicar 15+ piezas ancladas en gaps reales y pasar de rankear solo en texto a rankear en las 4 categorías universales de Google (texto / imágenes / video / shopping) en Colombia y USA como fase 1.

**El feature se ejecuta bajo el nuevo SEO Harness** (`.ia/seo/harness/`) que define SOUL, MÉTODO (RESEARCH → GAP_ANALYSIS → CONTENT_PLAN → GENERATE → PUBLISH → MONITOR → EVOLVE), POLÍTICAS, skills (content QC, model routing, data hygiene), workflows y prompts. El harness es la contraparte MYSEO de SPECBOOT (que queda para el código Laravel del repo).

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

### Fase 1 — Investigación keywords y gaps (semanas 2-3)

Consultas DataForSEO priorizadas. **Presupuesto de esta fase: $12 USD.**

**Batch 1.1 — Expansión de universo semántico ($4 USD):**
- `dataforseo_labs/google/keyword_ideas/live` — 5 semillas ES + 5 semillas EN → 200+ ideas
- `dataforseo_labs/google/keyword_suggestions/live` — variantes long-tail sobre las top 30
- `dataforseo_labs/google/related_keywords/live` — semánticamente relacionadas
- `dataforseo_labs/google/search_intent/live` — clasifica intent de las top 200

**Batch 1.2 — Volumen y dificultad ($3 USD):**
- `keywords_data/google_ads/search_volume/live` — volumen mensual país-por-país para 300+ keywords
- `dataforseo_labs/google/bulk_keyword_difficulty/live` — dificultad por batch

**Batch 1.3 — Rivales y gaps ($5 USD):**
- `dataforseo_labs/google/ranked_keywords/live` para 4 dominios × 2 países = 8 requests: ankofood, anko.com.tw, ferrero-machines, adlovermaquinas, empanadasmachine.
- `dataforseo_labs/google/domain_intersection/live` — Maqui vs cada rival, USA y COL.
- `dataforseo_labs/google/serp_competitors/live` — competidores agregados por keyword set.

**Salida:** `keywords_universe.csv` con 500+ keywords + `content_gaps.csv` con ≥100 gaps priorizados. Sin escribir nada aún.

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

**Piezas a producir (15+ en 12 semanas):**

| # | Tipo | Título tentativo | Idioma | Target keyword | Volumen |
|---|---|---|---|---|---|
| 1 | Pillar | Máquinas industriales para hacer empanadas: guía 2026 | ES | maquina para hacer empanadas | 480/mes |
| 2 | Pillar EN | Industrial empanada machines: complete buying guide | EN | empanada machine | 480/mes |
| 3 | Pillar | Máquinas para hacer arepas industriales | ES | maquina para hacer arepas | 210 USA / 590 COL |
| 4 | Cluster | Cuánto cuesta una máquina para hacer empanadas | ES | precio maquina empanadas | 480+ |
| 5 | Cluster | Máquina para hacer empanadas colombianas vs argentinas | ES | (gap) | TBD |
| 6 | Cluster | Cómo elegir entre CM05S, CM06 y CM06B | ES | (marca + comparación) | TBD |
| 7 | Local | Máquina para hacer empanadas en Miami | ES | maquiempanadas miami | 54 |
| 8 | Local | Máquina para hacer empanadas en Bogotá | ES | maquina empanadas bogota | 40 |
| 9 | Local | Empanada machines for restaurants in the US | EN | (gap) | TBD |
| 10 | Video | Reescribir descripciones + títulos SEO de 10 videos YouTube | ES/EN | varios | — |
| 11 | Product FAQ | FAQ ampliada para CM06B (top clics del sitio) | ES | (soporte a AC) | — |
| 12 | Cluster | Presupuesto para producción industrial de arepas | ES | (adyacente a "presupuesto 100 empanadas" que ya rankea) | TBD |
| 13 | Cluster | Ciclos de producción de una máquina de empanadas industrial | ES | (gap detectado en benchmark) | TBD |
| 14 | Cluster | Comparativa Maquiempanadas vs Anko vs Ferrero | ES/EN | (defensa de marca) | — |
| 15 | Local | Empanada machine dealer Texas / Florida | EN | (gap) | TBD |

Las columnas de volumen y gap concretas se completan tras Fase 1.

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

### Distribución del presupuesto $45 USD

| Fase | Costo | Acumulado |
|---|---:|---:|
| Fase 1 — Investigación | $12 | $12 |
| Fase 2 — Benchmark cross-category | $8 | $20 |
| Fase 3 — Auditoría + LLM baseline | $10 | $30 |
| Fase 4 — Monitoreo 3 meses + 2 rebenches LLM | $9 | $39 |
| Reserva iteraciones | $6 | $45 |

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
