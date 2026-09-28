---
slug: rankear-2026
proposed_at: 2026-09-20
last_updated: 2026-09-28 (revisión 4 — 9 mercados analizados con DataForSEO + tier de nichos por volumen real)
---

# Proposal — Rankear 2026

## Resumen

Rankear top 3 en 4 categorías universales de Google (texto / imágenes / video / shopping) atacando los **6 nichos culturales definidos por la cliente** + 2 mercados profesionales cruzados, reasignando esfuerzo por tier según el volumen real medido con DataForSEO el 2026-09-28. Los nichos siguen siendo intersecciones audiencia × geografía × producto identitario × vocabulario local: cubanos en USA (empanada cubana), puertorriqueños (empanadilla/pastelillo), costarricenses (chiverre/queso), chilenos (empanada de pino), venezolanos (harina de maíz + arepas) y colombianos en España.

**Tier de nichos por volumen del cluster core `maquina * empanadas` (17 variantes):**
- **Tier A (landing + cluster completo):** N1 Cubanos USA, N4 Chilenos (1,120/mes), N6 Colombianos España (790/mes), P2 profesional cross-mercado
- **Tier B (landing simple, sin cluster satélite):** N5 Venezolanos (200/mes, ancla en laminadora 590), P1 anglófono industrial USA
- **Tier C (vocabulario dentro de contenido latino USA, sin landing dedicada):** N2 Puertorriqueños (70/mes), N3 Costarricenses (100/mes)

Estrategia de **dos pilares de producto** que sigue vigente: CM06 (empanadas + arepas, puerta de entrada) para buyer inicial + CM5S (multifuncional con telemetría, competencia directa vs Anko) para buyer profesional. Cada nicho de Tier A y B recibe una landing específica que enlaza al pilar correspondiente según intención comercial. Los nichos Tier C se cubren dentro del contenido latino USA.

Combinar GSC, Ads, YouTube y CRM con investigación selectiva de DataForSEO bajo un techo acumulado inicial de **USD 15,50**, benchmark directo y un conjunto pequeño de analogías, y generación asistida por LLM con revisión humana, activos originales y autor identificado. **Las 10-11 piezas** (2 pilares + 3 landings Tier A + 2 landings Tier B + 3 posts cluster Tier A + 1 pilar EN condicional) forman un backlog; se publica primero una cohorte de 3-5 piezas (Tier A) y el resto se libera según evidencia.

**El feature se ejecuta bajo el SEO Harness** (`.ia/seo/harness/`) que define SOUL, MÉTODO (RESEARCH → GAP_ANALYSIS → CONTENT_PLAN → GENERATE → PUBLISH → MONITOR → EVOLVE), POLÍTICAS, skills (content QC con 13 checkpoints, model routing Opus/Sonnet/Haiku, data hygiene), workflows y prompts. El harness es la contraparte MYSEO de SPECBOOT (que queda para el código Laravel del repo).

## Cambios propuestos

### Revisión 3 — orden de ejecución y control económico

Esta revisión incorpora `seo-audit-maquiempanadas-com-2026-09-20.md` y prevalece sobre cualquier cifra o secuencia anterior que sobreviva en este documento.

1. **Corregir la base antes de crear escala:** validar el HTML/JSON-LD real; reparar mezcla de idiomas y `hreflang`; establecer autoría humana; completar alt text; implementar Product, VideoObject y Recipe/HowTo cuando los datos existan; retirar contenido caduco. FAQ se usa para responder decisiones del comprador, sin prometer rich results generales.
2. **Explotar fuentes propias gratuitas:** GSC, Ads, YouTube y CRM definen línea base, consultas, cohortes, etapas y resultado comercial. DataForSEO compra solamente evidencia que esas fuentes no entregan.
3. **Validar demanda e intención:** expandir por nicho con límites de resultados, deduplicar y enriquecer únicamente candidatos comerciales. No se exige producir 500 keywords ni 100 gaps.
4. **Validar SERP y competencia:** observar primero queries directas de maquinaria. Consultar categorías análogas solo para resolver una hipótesis concreta de formato.
5. **Pilotar contenido:** optimizar CM06/CM5S y publicar 3-5 piezas. Escalar el backlog hasta 15 únicamente con demanda, capacidad operativa y señal de avance.
6. **Medir negocio:** oportunidades orgánicas calificadas, conversión a venta e ingreso cobrado atribuible/asistido. Rankings, imágenes, video y LLMs son señales diagnósticas.

Presupuesto operativo inicial:

| Bloque | Rango máximo |
|---|---:|
| Gasto ya observado en JSON locales | USD 0,41 |
| Expansión y validación por nicho | USD 1,50-3,00 |
| SERPs directas, imágenes y popular products | USD 1,00-2,00 |
| Competidores y gaps seleccionados | USD 1,00-2,00 |
| Crawl técnico y páginas ganadoras | USD 0,25-1,00 |
| Baseline GEO/LLM limitado | USD 0,50-2,00 |
| Monitoreo inicial y contingencia | USD 2,50-5,00 |
| **Techo acumulado del primer ciclo** | **USD 15,50** |

Antes de cada batch se registra: endpoint, tareas, `limit`/`depth`, modo Live o Standard, tarifa estimada, costo máximo y decisión que habilita. Para trabajos no interactivos se prefiere la cola Standard; Live se reserva para exploración o verificación puntual.

### Fase 0 — Setup y baseline (semanas 1-2)

Trabajo de infraestructura. No cambia el sitio todavía.

- `.ia/seo/scripts/dataforseo_client.py` — cliente Python que envuelve las llamadas a DataForSEO, guarda JSON crudo con timestamp, escribe fila de costo en `COSTS.csv`.
- `.ia/seo/scripts/serp_snapshot.py` — captura SERP + guarda para comparación t0 vs t+N.
- `.ia/seo/scripts/llm_prompts/` — biblioteca de prompts de generación por tipo de contenido (pillar page, cluster post, product page, FAQ, alt-text batch).
- `.ia/seo/data/dataforseo/COSTS.csv` — control de gasto contra el techo acumulado inicial de $15,50.
- `.ia/seo/data/dataforseo/keywords_universe.csv` — inventario final tras fase 1 de investigación.
- `.ia/seo/data/dataforseo/content_gaps.csv` — gaps priorizados por demanda, intención, ajuste producto/mercado, valor, rankability y esfuerzo.
- `.ia/seo/data/dataforseo/benchmark_categories.csv` — resultados del recorrido cross-category (fase 3).
- `.ia/seo/data/dataforseo/llm_presence_baseline.md` — baseline t0.

### Fase 1 — Investigación keywords y gaps por nicho (semanas 2-3)

Consultas DataForSEO priorizadas y acotadas. **Techo de esta fase: USD 3,00**; el número de mercados no implica gastar el techo si los primeros datos descartan un nicho.

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

**Batch 1.1 — Expansión de universo semántico por nicho (techo USD 1,25):**
- `dataforseo_labs/google/keyword_ideas/live` — semillas culturales específicas por nicho:
  - N1: `empanada cubana`, `pastelito guayaba y queso`, `empanada de guayaba`
  - N2: `empanadilla`, `pastelillo`, `pastelillo de carne`
  - N3: `empanada tica`, `empanada de chiverre`, `empanada de queso costa rica`
  - N4: `empanada chilena`, `empanada de pino`, `empanada frita chilena`
  - N5: `empanada venezolana`, `harina PAN empanadas`, `empanada de queso venezolana`, `arepa`
  - N6: `empanadas colombianas España`, `empanadas colombianas Madrid`
  - P1: `empanada machine`, `commercial empanada machine`, `automatic empanada maker`
  - P2: `máquina industrial empanadas`, `línea producción empanadas semiautomática`
- `keyword_suggestions` o `related_keywords` solo se usan si `keyword_ideas` deja un vacío semántico específico; no se ejecutan por defecto para todas las semillas.
- `search_intent` se aplica después de deduplicar, únicamente a candidatos sin intención evidente.

**Batch 1.2 — Volumen y dificultad por país específico (techo USD 0,75):**
- `keywords_data/google_ads/search_volume/live` con el `location_code` correcto de cada nicho para las 30-50 keywords semilla de cada uno. 8 batches × 30-50 kw.
- `bulk_keyword_difficulty` se usa solo cuando la respuesta anterior no contenga una dificultad utilizable y únicamente sobre candidatos aprobados.
- **Salida clave:** volumen por nicho para priorizar. Probablemente Chile >> Costa Rica en volumen bruto; hay que confirmar con datos.

**Batch 1.3 — Rivales por nicho y gaps (techo USD 1,00):**
- `dataforseo_labs/google/ranked_keywords/live` para 5 dominios (ankofood, anko.com.tw, ferrero-machines, adlovermaquinas, empanadasmachine) × países prioritarios (USA + COL + los 4 nuevos donde tengan presencia) — hasta 10 requests.
- Rivales locales por nicho (a identificar en Fase 2 SERPs): para Chile, buscar `metalurgicavazquez.com.ar` o similares; para España, buscar competidores locales de maquinaria alimentaria.
- `dataforseo_labs/google/domain_intersection/live` — Maqui vs cada rival principal.
- `dataforseo_labs/google/serp_competitors/live` — competidores agregados por keyword set nicho-específico.

**Salida:** `keywords_universe.csv` con candidatos útiles clasificados por nicho + `content_gaps.csv` priorizado por `(demanda × intención × ajuste × valor × probabilidad_de_rankear) / esfuerzo`. Sin cuotas de filas y sin escribir contenido aún.

### Fase 2 — Benchmark cross-category (semanas 3-4)

Recorrer SERPs de nichos B2B con productos físicos caros para aprender **qué formatos ganan**. No es investigación de nuestra keyword, es investigación de nuestro tipo de negocio.

**Nichos a benchmarquear:**
- Máquinas de tortillas industriales (mercado latino directo adyacente)
- Máquinas de sushi / gyoza / dumpling (Anko compite ahí)
- Equipos de panadería industrial
- Extractoras de café comerciales
- Máquinas de helado industrial

**Techo de esta fase: USD 2,00.**

**Batch 2.1 — SERPs de referencia (techo USD 1,50):**
- Primero 24-36 SERPs comerciales directas en los mercados candidatos; ampliar solo si cambian la decisión.
- Hasta 12 SERPs análogas para hipótesis concretas de formato.
- Preferir `serp/google/organic/task_post` Standard para lotes; usar `live/advanced` cuando la respuesta inmediata sea necesaria. `depth=20` por defecto y 30 solo cuando la segunda o tercera página aporte evidencia.

**Batch 2.2 — Análisis de las top URLs ganadoras (techo USD 0,50):**
- Seleccionar 12-20 URLs que representen patrones distintos; no descargar automáticamente las tres primeras de cada SERP.
- Usar `on_page/instant_pages` o parsing solo cuando el HTML disponible no resuelva la pregunta.

**Salida:** `benchmark_categories.md` con patrones ganadores:
- Formatos de pillar page (H1-H6, longitud, imágenes, video embed, FAQ, tabla de specs)
- Uso de structured data
- Estrategias de cluster (¿cuántos posts satelitales por producto?)
- Formatos que aparecen en AI Overviews del nicho
- Estilos de imagen que ganan en Google Images para producto industrial
- Formatos de video que rankean

**Este es el input creativo del content plan** — no se inventa qué escribir, se aprende de quien ya gana.

### Fase 3 — Content plan asistido por LLM (semanas 4-6)

**Techo DataForSEO de esta fase: USD 3,00.** La auditoría pública existente sustituye el crawl exploratorio inicial; DataForSEO se usa para verificar hallazgos dudosos o medir lo que el fetch no pudo observar.

**Batch 3.1 — Verificación técnica dirigida (techo USD 1,00):**
- `on_page/summary` + `on_page/duplicate_content` sobre `maquiempanadas.com` para detectar qué URLs actuales pueden expandirse (quick wins) vs. crear nuevas.
- `on_page/broken_resources` para detectar imágenes rotas / videos mal servidos.

**Batch 3.2 — Presencia en LLMs baseline (techo USD 2,00):**
- `ai_optimization/{chat_gpt|claude|gemini|perplexity}/llm_responses/live` — 5-8 queries comerciales congeladas; registrar modelo, configuración, fecha, tokens y costo real.
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
   - Output: 8-12 pares Q/A útiles para el comprador; usar `FAQPage` solo si cumple las políticas y sin prometer elegibilidad para rich results.

4. **`alt_text_batch.md`** — alt-text bilingüe para imágenes existentes:
   - Input: URL de imagen + contexto de página
   - Output: alt-text descriptivo ES + EN

5. **`local_landing.md`** — landing por ciudad (Miami, Houston, Bogotá, Medellín, Manizales):
   - Input: ciudad + diáspora relevante + queries locales
   - Output: página con contenido geo-específico; `LocalBusiness` solo cuando la URL represente una sede física real con NAP verificable.

6. **`video_description.md`** — descripciones de YouTube + títulos SEO:
   - Input: URL video + producto + keywords objetivo
   - Output: título, descripción, tags, timestamp chapters

**Cada draft pasa por 3 filtros antes de publicar:**
1. **Filtro humano** — Nicolás o editor revisa: veracidad, tono, links.
2. **Filtro E-E-A-T** — checklist: ¿hay autor identificado?, ¿fotos originales?, ¿video propio?, ¿testimonio verificable?.
3. **Filtro Rich Results Test** — el schema pasa validación de Google.

**Backlog máximo de piezas (hasta 15, liberadas por cohortes):**

### Pilares master (2)

| # | Tipo | Título tentativo | Idioma | Producto | Target keyword | Rol |
|---|---|---|---|---|---|---|
| 1 | Pillar master | Máquinas para hacer empanadas y arepas: guía completa | ES | **CM06** | `maquina para hacer empanadas y arepas` | Puerta de entrada — buyer inicial |
| 2 | Pillar master | Máquina profesional multifuncional para empanadas — CM5S con telemetría | ES | **CM5S** | `maquina industrial empanadas`, `linea produccion empanadas` | Comercial premium — buyer profesional |

### Landing pages nicho-específicas (Tier A: 3 + Tier B: 2 = 5)

Una por audiencia con volumen suficiente. Cada una con vocabulario local, foto/video del producto identitario, testimonio de cliente real del nicho, LocalBusiness schema apuntando a la bodega que despacha (USA para N1/N2, COL para N3/N4/N5, decidir por logística para N6). Todas enlazan al pilar CM06 o CM5S según intención comercial.

**Tier A — landing dedicada + cluster satélite** (nichos donde el volumen cluster core justifica esfuerzo completo):

| # | Tipo | Nicho | Tier | Target keyword | Producto | Bodega |
|---|---|---|---|---|---|---|
| 3 | Landing nicho | N1 Cubanos en USA | A | `maquina para hacer empanadas cubanas`, `maquina pastelitos guayaba y queso` | CM06 (arranque) + link a CM5S | USA |
| 4 | Landing nicho | N4 Chilenos | A | `maquina para hacer empanadas chilenas`, `maquina empanadas de pino`, `fabrica de empanadas chile` (1,120 core + 930 fábrica/mes) | CM06 + CM5S | Colombia |
| 5 | Landing nicho | N6 Colombianos en España | A | `maquina empanadas colombianas Madrid`, `venta maquina empanadas España` (790 core + 890 laminadora/mes) | CM5S (buyer profesional) | USA o COL según costo |

**Tier B — landing simple sin cluster satélite** (volumen suficiente para landing propia pero no para invertir en cluster):

| # | Tipo | Nicho | Tier | Target keyword | Producto | Bodega |
|---|---|---|---|---|---|---|
| 6 | Landing nicho | N5 Venezolanos | B | `maquina empanadas venezolanas`, `laminadora de masa venezuela` (200 core + 590 laminadora/mes — laminadora es ancla real) | CM06 con enlace a laminadora | Colombia |
| 7 | Landing EN | P1 Anglófono industrial | B | `commercial empanada machine`, `automatic empanada maker machine` | CM5S en inglés | USA |

**Tier C — vocabulario cubierto dentro de contenido latino USA sin landing dedicada** (volumen cluster core insuficiente para landing propia):

- **N2 Puertorriqueños** (70/mes cluster core en la isla) — vocabulario `empanadilla`, `pastelillo` y variantes se incorporan como sección dentro de la landing N1 Cubanos USA (que también aplica a la diáspora puertorriqueña en USA mainland). Sin landing propia.
- **N3 Costarricenses** (100/mes cluster core) — vocabulario `empanada tica`, `empanada de chiverre` se incorpora como sección dentro del post cluster N1 o del blog latino USA. Sin landing propia.

### Posts cluster por nicho (Tier A: 3)

Uno por cada nicho Tier A, tipo "cómo empezar negocio de [empanada del nicho] en [ciudad]". Enlazan a la landing nicho + al pilar correspondiente. Formato blog SEO informacional que refuerza el pilar. Los nichos Tier B y C **no reciben post cluster** en este ciclo (validar antes de invertir).

| # | Tipo | Nicho | Tier | Título tentativo | Target keyword |
|---|---|---|---|---|---|
| 8 | Cluster | N1 | A | Cómo montar un negocio de pastelitos cubanos en Miami (incluye sección puertorriqueña con empanadillas) | `negocio empanadas cubanas Miami`, `franquicia pastelitos`, `empanadilla puertorriqueña miami` |
| 9 | Cluster | N4 | A | Producción industrial de empanadas de pino en Chile | `empanadas chilenas al por mayor`, `fabrica empanadas Chile` |
| 10 | Cluster | N6 | A | Empanadas colombianas para hostelería en España | `empanadas colombianas mayorista España`, `distribuidor empanadas Madrid` |

### Pilar en inglés (opcional, dentro de Tier B)

La landing #7 anterior cumple el rol de landing P1. Si tras Fase 1 el volumen anglófono industrial justifica un pilar completo (no solo landing), se agrega como pieza #11:

| # | Tipo | Mercado | Título tentativo | Target keyword |
|---|---|---|---|---|
| 11 (condicional) | Pillar EN | P1 Anglófono industrial USA | Commercial empanada machines: complete buying guide (CM5S) | `commercial empanada machine`, `automatic empanada maker machine` |

### Resumen de piezas

- **2 pilares master ES** (CM06, CM5S) — obligatorios
- **3 landings Tier A** (N1, N4, N6) — obligatorios
- **2 landings Tier B** (N5, P1) — obligatorios pero simples
- **3 posts cluster Tier A** (N1 con inclusión de N2, N4, N6) — obligatorios
- **1 pilar EN** (condicional post-Fase 1)

**Total backlog: 10-11 piezas** (antes 15). Reducción de 4-5 piezas vs revisión 3 al degradar N2 y N3 a Tier C (sin landing propia) y consolidar el cluster de la diáspora puertorriqueña dentro del post N1.

### Optimizaciones colaterales incluidas en el ciclo (no cuentan como "piezas" pero son entregables)

- **Product FAQ ampliada** para CM06 y CM5S existentes con las 4 PAA del SERP objetivo de cada uno.
- **Rewrite de descripciones + títulos SEO** de 10 videos YouTube del canal.
- **Auditoría y fix de alt-text** en imágenes existentes de páginas de producto (Haiku batch).
- **hreflang correcto** para las 6 landings nicho + `/en/` + `/es/` base.
- **Structured data adicional** (LocalBusiness apuntando a bodega, Product/Offer, VideoObject) en las páginas nuevas.

Las columnas de volumen y gap concretas se completaron el 2026-09-28 con el batch DataForSEO de 9 mercados. **Reglas de asignación por tier ya aplicadas** en este documento (revisión 4):
- Tier A recibe landing + cluster (N1, N4, N6 y el pilar P2 profesional).
- Tier B recibe landing simple sin cluster (N5, P1).
- Tier C se cubre dentro del contenido de N1 sin landing dedicada (N2, N3).
- Argentina queda documentada sin proponer como mercado objetivo (ver Fuera de alcance en 02-refined.md).

Si tras Fase 1 los datos actualizados muestran que algún nicho Tier B tiene señal comercial fuerte (leads calificados o intención de compra clara), se puede promocionar a Tier A con aprobación de la cliente.

### Fase 4 — Publicación técnica y monitoreo (semanas 6-24)

**Techo DataForSEO de esta fase dentro del primer ciclo: USD 5,00.** Después del primer ciclo, cada extensión se decide con datos de oportunidades y ventas.

**Batch 4.1 — Publicación (sin DataForSEO):**
- Merge cada pieza publicada con: schema válido, alt-text, structured data, canonical, hreflang correcto.
- Video schema fix para los 37 videos con incidencia (fase de diagnóstico caso por caso, no fix ciego).

**Batch 4.2 — Monitoreo mensual (techo inicial USD 3,00):**
- GSC es la fuente primaria y gratuita para consultas, páginas, países, imágenes y video.
- DataForSEO verifica mensualmente 15-25 combinaciones keyword × mercado aprobadas, no todos los gaps.
- Preferir cola Standard; usar Live para comprobaciones puntuales. Registrar `depth` y costo real de cada tarea.
- `historical_rank_overview` es opcional y su costo se calcula con la tarifa vigente; no se presupone que una tarea cueste USD 0,05.

**Batch 4.3 — Rebench de presencia en LLMs a t+3m y t+6m (techo USD 2,00 dentro del primer ciclo):**
- Mismas 5-8 queries y configuración del baseline. No interpretar una respuesta API como representación exacta de todos los usuarios del producto de consumo.

### Distribución del presupuesto DataForSEO (revisión 3)

| Fase | Techo incremental | Acumulado máximo aproximado* |
|---|---:|---:|
| Gasto ya observado | — | $0,41 |
| Fase 1 — Investigación y gaps | $3,00 | $3,41 |
| Fase 2 — SERPs y benchmark dirigido | $2,00 | $5,41 |
| Fase 3 — Verificación técnica + LLM baseline | $3,00 | $8,41 |
| Fase 4 — Monitoreo y rebench inicial | $5,00 | $13,41 |
| Contingencia del primer ciclo | $2,09 | **$15,50** |

\*El acumulado se concilia contra `cost` de las respuestas reales. Los techos no son objetivos de gasto. El saldo del depósito de USD 50 se conserva para iteraciones que demuestren una decisión o resultado adicional.

## Contratos afectados

- **`maquiempanadas.com` HTML/JSON-LD:** cada URL nueva agrega structured data sustentado (Product, VideoObject, Recipe/HowTo, FAQPage o LocalBusiness cuando corresponda). `offers`, `aggregateRating`, testimonios, NAP y disponibilidad requieren evidencia real. Los cambios se validan en staging.

- **YouTube channel:** descripciones y títulos de 10+ videos se reescriben para SEO. Contrato con la audiencia YouTube no se rompe — el contenido del video no cambia.

- **Google Search Console:** no hay contrato que romper, es unidireccional.

## Alternativas descartadas

- **Alternativa A: sólo SEO técnico + esperar que rankeen las páginas actuales.** Descartada porque el sitio ya tiene 18 kw en pos 1 y las páginas actuales no cubren gaps donde rivales rankean top 10. Sin contenido nuevo, el techo está cerca.

- **Alternativa C: traducir el blog existente al inglés.** Descartada porque el 81% del tráfico USA actual es en español (diáspora latina) — traducir al inglés no capta ese comprador. Contenido en inglés se hace nuevo y original si hay gap real.

- **Alternativa D: generar todo el contenido con LLM sin revisión humana.** Descartada por E-E-A-T y por el riesgo de inventar specs de productos.

- **Alternativa E: contratar redactores externos sin usar LLM.** Descartada por velocidad. LLM acelera el draft; humano firma y corrige.

- **Alternativa F: seguir el SPEC anterior (`SPEC_dataforseo_50usd.md`) que priorizaba investigación técnica sobre contenido.** Descartada — su meta era "producir listas y CSVs", no "rankear". Esta propuesta absorbe sus mejores partes (investigación de gaps, backlinks) y las subordina al objetivo de contenido publicado.

## Plan de tests

Estos "tests" son de resultado SEO, no unit tests de código.

- **Verificación mensual automatizada:**
  - GSC aporta la serie primaria. Un script captura DataForSEO solo para 15-25 combinaciones keyword × mercado aprobadas.
  - Genera diff mensual en `.ia/seo/monitoring/YYYY-MM/diff.md`; no se reconsulta cada gap semanalmente.

- **Verificación por AC:**
  - **AC1** (universo útil) → verificar cobertura de los ocho segmentos, deduplicación, fuentes y campos de decisión. Deadline: semana 3.
  - **AC2** (gaps priorizados) → revisar fórmula, evidencia y decisión recomendada; no contar filas como proxy de calidad. Deadline: semana 3.
  - **AC3** (keywords objetivo en top 3) → GSC + snapshots propios de SERP en t0/t+3m/t+6m.
  - **AC4** (imágenes pos ≤10 USA, ≤6 COL) → GSC image + `serp/google/images/live/advanced` a t+3m y t+6m.
  - **AC5** (video ≥1,000 impr/3m) → GSC video a t+3m y t+6m.
  - **AC6** (3 kw con producto en Popular products) → `serp/google/organic/live/advanced` a t+3m y t+6m.
  - **AC7** (cohorte piloto 3-5; backlog condicionado) → inventario, aprobación, publicación y decisión de escalar en `.ia/seo/content_ledger.md`.
  - **AC8** (video schema válido) → Rich Results Test manual + reporte GSC Video Indexing.
  - **AC9** (LLM presence baseline + rebench) → `llm_presence_baseline.md`, `llm_presence_t3m.md`, `llm_presence_t6m.md`.
  - **AC10** (rastro JSON) → `.ia/seo/data/dataforseo/` + `COSTS.csv`.
  - **AC11** (primer ciclo acumulado ≤$15,50) → conciliar `COSTS.csv` con el campo `cost` de cada JSON y documentar cualquier nueva aprobación.
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

### D. Descubrimiento e indexación compatibles con Google

La Indexing API de Google no se usa para estas páginas: está limitada a `JobPosting` y `BroadcastEvent` dentro de `VideoObject`. Después de publicar se actualiza el sitemap con `lastmod`, se comprueba canonical y enlazado interno, y se usa "Request Indexing" en Search Console solo para las pocas URL prioritarias. La fecha y el resultado se registran en el `trajectory.md`.

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

Las 5 adiciones **no aumentan el techo acumulado inicial de $15,50 USD** del feature:
- A y E son cambios organizativos (más disciplina, no más gasto).
- B redistribuye el gasto LLM entre modelos — probablemente ahorra por usar Haiku donde antes usaría Sonnet por default.
- C es análisis sobre datos GSC que ya tenemos.
- D usa sitemap, enlazado interno y Search Console; no usa indebidamente la Indexing API.

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
