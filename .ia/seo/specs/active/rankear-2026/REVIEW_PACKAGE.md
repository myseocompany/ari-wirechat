# SPEC — Rankear 2026 (feature completo para revisión externa)
**Ubicación en el repo:** `.ia/seo/specs/active/rankear-2026/`  **Framework:** SEO Harness — MYSEO-style (SOUL/METHOD/POLICIES/skills/workflows) en `.ia/seo/harness/`  **Estado:** refined + proposed (revisión 2 con audiencias culturales y estrategia dos pilares)  **Fecha:** 2026-09-20
---
## Cómo leer este documento
Este archivo consolida los 3 artefactos del ciclo SPECBOOT-adaptado del feature `rankear-2026`:
1. **User Story raw** (01) — la petición original + las respuestas de la cliente + contexto ya diagnosticado.
2. **Refined User Story** (02) — Actor, Valor, definición de 6 nichos culturales + 2 mercados profesionales, estrategia de dos pilares (CM06 + CM5S), 12 Criterios de Aceptación, requisitos no funcionales, supuestos (confirmados + pendientes), riesgos, fuera de alcance.
3. **Proposal técnico** (03) — el plan concreto de 4 fases + presupuesto $45 USD + adiciones basadas en mejores prácticas SEO 2026 + 15 piezas de contenido asignadas a nichos.

**Cambios respecto a revisión 1:** el SPEC se reencuadró tras la definición de 6 nichos culturalmente específicos por parte de la cliente. Los AC se rehicieron por nicho, el content plan se reasignó, y se sumó la estrategia de dos pilares de producto tras revisar los datos del sitio.

**Preguntas al revisor** al final del documento.

---

# PARTE 1 — User Story raw

`01-user-story.md`

---
slug: rankear-2026
title: Rankear Maquiempanadas en Google top 3 por país y por categoría universal
status: refined
created: 2026-09-20
author: nicolas@myseocompany.co
---

# User Story (raw)

Como responsable de crecimiento de Maquiempanadas, quiero **rankear en Google top 3 en las keywords comerciales que traen compradores de máquinas**, para cada país donde el negocio puede vender y en cada categoría de SERP (texto, imágenes, video, shopping), **para que las ventas orgánicas suban de forma sostenida sin depender exclusivamente de Google Ads**.

## Contexto crudo

### Petición original de Nicolás (MySEO)

> "como objetivo pusiste desarrollar aspectos técnicos de SEO. No debería ser rankear? en cada país? en cada categoría (texto, imágenes, video, shop)? Creo que el contenido debe ser la meta, encontrar los elementos del SEO técnico pero desarrollar un plan de contenidos que nos posicione. Tal vez hacer benchmark de otra categoría para aprender qué funciona, recorrer 10 o 100 resultados sacando ideas para luego con LLM generar contenidos. PIENSA! busca las mejores prácticas para 2026"

> "compre 50USD [en DataForSEO] y no los quiero perder"

### Definición de audiencias (mensaje literal de la cliente, 2026-09-20)

> "Cubanos en Estados Unidos
> Puerto riqueños
> Costarricenses
> Chilenos
> Venezolanos en Venezuela
> Colombianos en España"

### Respuestas de la cliente a las preguntas operativas (2026-09-20)

1. Hay envío internacional.
2. Hay bodega en Colombia y en USA.
3. Los modelos actuales hacen todas esas empanadas.
4. La cliente quiere rankear el **CM5S** (mejor rentabilidad para MQE, multifuncional que compite con Anko, semiautomática con datos por internet). Nicolás sugiere el **CM06** (más pequeño, solo arepas y empanadas) como puerta de entrada. Estrategia acordada: **dos pilares complementarios** — CM06 para buyer inicial + CM5S para buyer profesional.
5. Adaptación de idioma por nicho: aprobada.

### Contexto ya diagnosticado en `analisis_keywords_gsc.md` (v7) y `reporte_maquiempanadas.html`

- Sitio con 18 keywords en posición #1 en USA (español), 145 keywords rankeando en Anko (rival taiwanés).
- Búsqueda universal: web 75.6% del share, imágenes 24.4% (posición promedio 21-36), video 0.005% (33 impresiones en 16m con 37 videos con incidencia).
- Canal YouTube con 3.77M views totales y 1.18M en los últimos 12 meses.
- CM5S ya es el mejor posicionado orgánicamente: pos 4.56 web, activo #1 en Imágenes (287 clics / 20,398 impresiones). CM06 pos 5.27 web / 225 clics. CM06B pos 7.37 web / 203 clics.
- Presencia en respuestas de IA generativa creciendo (+65% jun→sep 2026).
- Presupuesto $50 USD en DataForSEO ya comprado, cuenta verificada. ~$0.38 gastados en batch inicial.

## Enlaces

- Diagnóstico previo: `.ia/seo/analisis_keywords_gsc.md`
- Reporte cliente: `.ia/seo/reporte_maquiempanadas.html`
- Explorador SERPs: `.ia/seo/serps_maquiempanadas.html`
- Meta comercial global: `GOAL.md` (40 máquinas Maquiempanadas / mes)
- Interlocutores: Nicolás Navarro (MySEO), cliente Maquiempanadas


---

# PARTE 2 — Refined User Story

`02-refined.md`

---
slug: rankear-2026
refined_at: 2026-09-20
last_updated: 2026-09-20 (revisión 2 — audiencias culturales + estrategia dos pilares)
---

# Refined User Story — Rankear 2026

## Actor

**Quién ejecuta:** MySEO Company (Nicolás Navarro + agentes IA) sobre el sitio `maquiempanadas.com` y el canal YouTube `Maquina Empanadas Maquiempanadas Colombia`.

**Quién se beneficia:** el negocio Maquiempanadas — traducido en más ventas orgánicas de máquinas industriales (empanadas, arepas, moldes, desmechadora, laminadora).

Son actores distintos: MySEO ejecuta, Maquiempanadas cobra.

## Valor

**Problema real:** hoy Maquiempanadas gasta ~$1,890 USD/mes en Google Ads (Colombia + PMax USA + Search LATAM + otras). ~54.6% de las conversiones reveladas en Ads vienen de tráfico **de marca** — es demanda que capturaría el orgánico gratis si estuviera bien posicionado. El resto son keywords genéricas donde el CPA sube a $6,500-14,900 COP.

**Además:** el mercado tiene 4 categorías de SERP simultáneas (texto, imágenes, video, shopping) y el sitio hoy solo rankea decentemente en texto. Perder las otras 3 categorías es dejar cuota de voz en la mesa.

**Cuantificación:**
- Si SEO orgánico captura al menos el 30% de las conversiones que hoy hace Ads (unas 300 conv/mes), se liberan ~$500 USD/mes de presupuesto de Ads que puede ir a keywords donde SEO no puede llegar.
- Sumar visibilidad en imágenes (24% del share hoy en pos 21-36) puede duplicar clics del cluster de producto sin crear una sola URL nueva.
- Rankear en video reconecta un canal con 1.18M views/año que hoy no aporta al sitio.

**Meta de negocio de referencia** (`GOAL.md`): 40 máquinas de Maquiempanadas vendidas al mes. SEO no es único motor, pero puede aportar entre 8-15 de esas 40 en régimen consolidado (estimación conservadora basada en el funnel actual — pendiente de validar con datos del CRM).

## Audiencias objetivo (revisión 2 — 2026-09-20)

La cliente definió 6 nichos culturalmente específicos + 2 mercados profesionales cruzados. No son "países", son intersecciones **audiencia × geografía × producto identitario × vocabulario**:

| Nicho | Audiencia | Reside en | Producto identitario | Vocabulario clave | Google | Bodega envía |
|---|---|---|---|---|---|---|
| N1 | Cubanos | USA (Miami, NJ, NY) | Empanada cubana (guayaba y queso, pastelito) | "empanada cubana", "pastelito guayaba" | google.com · ES | USA |
| N2 | Puertorriqueños | Puerto Rico + USA mainland | **Empanadilla / pastelillo** (no "empanada") | "empanadilla", "pastelillo puertorriqueño" | google.com · ES | USA |
| N3 | Costarricenses | Costa Rica | Empanada tica (chiverre, queso) | "empanada tica", "empanada de chiverre" | google.co.cr | Colombia |
| N4 | Chilenos | Chile | Empanada de pino (carne + cebolla + aceituna + huevo) | "empanada de pino", "empanada chilena" | google.cl | Colombia |
| N5 | Venezolanos | Venezuela | Empanada de harina de maíz amarilla + arepas | "empanada venezolana", "harina PAN", "arepa" | google.co.ve | Colombia |
| N6 | Colombianos | España (Madrid, Barcelona) | Empanada colombiana frita (maíz) | "empanadas colombianas Madrid" | google.es · ES | USA o COL |
| P1 | Comprador industrial en inglés | USA anglófono | Cualquier máquina (foco CM5S) | "empanada machine", "commercial empanada machine" | google.com · EN | USA |
| P2 | Empresa / franquicia hispanohablante | LATAM extendido + España | CM5S multifuncional con telemetría | "máquina industrial empanadas", "línea producción empanadas" | multi | según país |

## Estrategia de dos pilares de producto

Los datos del sitio (GSC últimos 3 meses) validan lo que ya pasa orgánicamente:

- **CM5S:** pos 4.56 en web, activo #1 en Imágenes (287 clics / 20,398 impresiones). Es la keyword-magnet natural.
- **CM06 (empanadas + arepas):** pos 5.27 en web, 225 clics. Es el "primer producto" que compra un buyer nuevo.
- **CM06B (multifuncional):** pos 7.37 en web con 11,813 impresiones — demanda de "multifuncional" mal capturada.

Los dos pilares se complementan como escalera de intención:

| Pilar | Rol | Buyer objetivo | Query cabeza |
|---|---|---|---|
| **CM06 (empanadas + arepas)** | Puerta de entrada | Negocio pequeño / familiar / arranque | `maquina para hacer empanadas y arepas` (genérica, alto volumen) |
| **CM5S (multifuncional + telemetría)** | Comercial premium | Fábrica / franquicia / expansión | `maquina industrial empanadas`, `empanada machine`, `commercial empanada machine` |

Cada pilar tiene páginas nicho-específicas debajo (una por nicho cultural), enlazando hacia arriba al pilar correspondiente según intención comercial detectada.

## Criterios de aceptación

Formato Gherkin. Cada AC verificable programáticamente contra datos de DataForSEO / GSC.

### Categoría: cobertura de keywords rankeando

- **AC1 — Universo de keywords mapeado.** Given las semillas de Maquiempanadas y sus rivales, When se ejecuta la investigación de DataForSEO Labs, Then existe un CSV `keywords_universe.csv` con **≥500 keywords** clasificadas por país (USA/COL/MEX/ARG/ESP), idioma (ES/EN), intent (info/comm/trans/nav), volumen, dificultad y presencia actual de Maquiempanadas.

- **AC2 — Content gaps identificados.** Given el universo de AC1, When se cruza con las keywords donde `ankofood.com`, `adlovermaquinas.com` y `ferrero-machines.com` rankean top 10 y Maquiempanadas no aparece en top 20, Then existe `content_gaps.csv` con **≥100 gaps** priorizados por (volumen × posición_rival).

### Categoría: rankings objetivo por país + categoría universal

- **AC3 — Meta de posición web (texto) por nicho.** Al terminar el ciclo (horizonte 6 meses tras publicar contenidos), Maquiempanadas debe estar en **top 3 orgánico** en al menos **12 keywords comerciales nicho-específicas** (2 por cada uno de los 6 nichos culturales) + al menos **3 keywords adicionales del cluster profesional** (P1/P2). Distribución:
  - **N1 Cubanos en USA:** 2 keywords en top 3 (ej. `maquina para hacer empanadas cubanas`, `maquina pastelitos cubanos`).
  - **N2 Puertorriqueños:** 2 keywords en top 3 (ej. `maquina para hacer empanadillas`, `maquina pastelillos puerto rico`).
  - **N3 Costarricenses:** 2 keywords en top 3 (ej. `maquina empanadas costa rica`, `maquina empanadas de chiverre`).
  - **N4 Chilenos:** 2 keywords en top 3 (ej. `maquina empanadas chilenas`, `maquina empanadas de pino`).
  - **N5 Venezolanos:** 2 keywords en top 3 (ej. `maquina empanadas venezolanas`, `maquina harina PAN empanadas`).
  - **N6 Colombianos en España:** 2 keywords en top 3 (ej. `maquina empanadas colombianas madrid`, `venta maquina empanadas españa`).
  - **P1 Anglófono industrial:** al menos 1 keyword en top 3 (ej. `commercial empanada machine`, `automatic empanada machine`).
  - **P2 Profesional hispano cross-país:** al menos 2 keywords en top 3 (ej. `linea produccion empanadas industrial`, `maquina empanadas semiautomatica`).
  
  Las 18 keywords en posición #1 ya existentes se mantienen — meta de defensa: **no perder ninguna**. Medición: `dataforseo_labs/google/historical_serps/live` comparando t0 vs t+6m. Keywords finales se cierran en Fase 1 del proposal con volumen y competencia verificados.

- **AC4 — Meta de imágenes por nicho.** El cluster comercial en Google Images (queries con producto identitario de cada nicho + `máquina` / `molde` / `arepa`) debe mover su posición promedio ponderada por impresiones a **≤10 en al menos 4 de los 6 nichos** al final del ciclo. Prioridad de auditoría de imágenes: CM5S (activo #1 hoy) + CM06 + CM06B con alt-text nicho-específico ("máquina para hacer empanadillas puertorriqueñas" ≠ "máquina para hacer empanada de pino"). Medición: GSC `search type = image` + Países + DataForSEO `serp/google/images/live/advanced`.

- **AC5 — Meta de video.** Los videos indexados del sitio deben pasar de **33 impresiones/16m a ≥1,000 impresiones/3m** en Google Search (categoría video) — reflejando que el canal YouTube (1.18M views/año) empieza a impactar el SEO web. Medición: GSC `search type = video`.

- **AC6 — Meta de Shopping / popular products.** En al menos 3 keywords cabeza (`maquina para hacer empanadas`, `empanada machine`, `maquina para hacer arepas`), un producto de Maquiempanadas debe aparecer en el bloque "Popular products" de Google. Hoy: 0. Requiere activar Google Merchant Center con feed de productos correctamente estructurado (CM06 y CM5S como prioridad). Medición: `serp/google/organic/live/advanced` (feature `popular_products`).

### Categoría: contenido publicado

- **AC7 — Content plan ejecutado.** Deben publicarse en `maquiempanadas.com` **≥15 nuevas piezas de contenido**, distribuidas así:
  - **2 pillar pages master:** CM06 (empanadas + arepas, puerta de entrada) y CM5S (multifuncional profesional). En español base.
  - **6 landing nicho:** una por audiencia cultural (N1-N6). Cada una con vocabulario, foto/video del producto identitario, testimonio local, LocalBusiness schema apuntando a la bodega que envía.
  - **6 posts cluster:** uno por nicho, tipo "cómo empezar negocio de empanadas [tipo] en [ciudad]", enlazando al pilar y a la landing nicho correspondiente.
  - **1 pillar en inglés** (CM5S) para P1 anglófono industrial.
  Cada pieza con: alt-text descriptivo en idioma/dialecto del nicho, structured data válido (Product / Video / FAQ / LocalBusiness según aplique), imagen original o video embed, tabla de datos, autor identificado, hreflang correcto por nicho.

- **AC8 — Video schema corregido.** Los 37 videos con incidencia "no está en página de visualización" deben pasar auditoría de Rich Results Test de Google. Medición: reporte GSC de Video Indexing.

- **AC9 — Presencia en LLMs medida y con línea base.** Existe `llm_presence_baseline.md` con matriz `10 queries × 4 LLMs` mostrando: (a) si Maquiempanadas es citada, (b) qué otros dominios son citados, (c) score de mención. Baseline t0 medida antes de publicar contenido nuevo; remedición a t+3m y t+6m.

### Categoría: proceso y trazabilidad

- **AC10 — Cada consulta DataForSEO deja rastro.** JSON crudo en `.ia/seo/data/dataforseo/YYYY-MM-DD_<endpoint>_<slug>.json` + fila en `.ia/seo/data/dataforseo/COSTS.csv` con costo real.

- **AC11 — Presupuesto DataForSEO no supera $45 USD** (deja $5 de margen sobre los $50 comprados) en la fase de investigación. Costos de monitoreo mensual posterior se contabilizan aparte.

- **AC12 — Cada pieza de contenido nace de un gap identificado.** No se publica un post o página sin referencia explícita a la keyword/gap que ataca en `content_gaps.csv`.

## Requisitos no funcionales

- **Ética de contenido:** todo contenido generado con LLM debe pasar revisión humana antes de publicar. Nunca hay que publicar afirmaciones sobre productos, precios o capacidades sin validar con Maquiempanadas.

- **E-E-A-T 2026:** cada pieza debe demostrar experiencia real:
  - Fotos originales de las máquinas (no stock).
  - Video real de operación cuando aplique.
  - Autor identificado (persona real de Maquiempanadas).
  - Testimonios verificables cuando se mencionen.

- **AI-first content:** cada pieza pensada para poder ser citada en AI Overviews / AI Mode / respuestas de ChatGPT/Claude/Gemini/Perplexity:
  - Respuestas directas y factuales al inicio.
  - Datos concretos (números, precios, capacidades).
  - Structured data completo (Product, Video, FAQ, HowTo, LocalBusiness cuando aplique).
  - "Entidades" reconocibles (marca, modelos, categorías).

- **Multi-idioma:** ES por default. EN en `/en/` con contenido original (no traducción literal), no obligatorio en cada pieza. PT `/pt/` sin inversión nueva.

- **Structured data:** cada URL nueva o modificada debe pasar Google Rich Results Test antes de mergear.

- **Mobile-first:** 79% de las impresiones son móvil. Diseño y tiempos de carga optimizados para móvil.

- **Accesibilidad:** alt-text descriptivo en TODAS las imágenes, no solo las nuevas.

- **Confidencialidad:** ninguna información sensible del CRM (leads, ventas específicas por cliente, precios negociados) puede salir en contenido público.

## Supuestos

### Confirmados por la cliente (2026-09-20)

- [✓ CONFIRMADO] **Envío internacional disponible** — Maquiempanadas envía a los 6 nichos culturales (USA, Puerto Rico, Costa Rica, Chile, Venezuela, España).
- [✓ CONFIRMADO] **Bodegas operativas en Colombia y USA** — permite promesa de entrega diferenciada por nicho (bodega USA para N1/N2/P1; bodega COL para N3/N4/N5/N6).
- [✓ CONFIRMADO] **Los tres modelos (CM06, CM06B, CM5S) hacen todas las variantes culturales** de empanada (cubana, puertorriqueña, tica, chilena, venezolana, colombiana). No hay que crear producto nuevo.
- [✓ CONFIRMADO] **Producto foco definido:** estrategia de dos pilares — CM5S para buyer profesional/industrial (postura de la cliente) + CM06 para buyer de arranque (postura MySEO). Ambos con landings nicho-específicas debajo. Los datos de GSC ya validan que ambos rankean bien.
- [✓ CONFIRMADO] **Adaptación de idioma por nicho aprobada** — se harán landings con vocabulario local (empanadilla/pastelillo, pino, chiverre, harina PAN, etc.), no traducción literal del sitio.

### Pendientes de confirmar

- [CONFIRMAR] **Maquiempanadas puede producir contenido audiovisual original nicho-específico** — 6 nichos requieren 6 sets de fotos/video mostrando el producto identitario (empanada de pino chilena, empanadilla puertorriqueña, etc.). ¿Se puede producir? Alternativa: grabar en la bodega colombiana operando el molde adecuado para cada variante.
- [CONFIRMAR] **Existe autor humano identificable** que firme los posts para E-E-A-T. Preferible: alguien del equipo de Maquiempanadas con cargo (ejemplo: "Ingeniero de aplicaciones" o "Jefe comercial"). Sin autor firmante, E-E-A-T queda débil.
- [CONFIRMAR] **Testimonios reales por nicho** — ¿tienen clientes recurrentes en cada uno de los 6 nichos que puedan aparecer citados (nombre + ciudad + tipo de negocio)? Sin testimonio real por nicho, el AC7 queda cojo.
- [CONFIRMAR] **Precios finales de CM06 y CM5S** para páginas de producto por nicho. Pueden variar por moneda / logística (bodega USA vs COL). Impacta structured data Product/Offer.
- [CONFIRMAR] **Presupuesto para redactor/editor bilingüe humano** — LLM produce drafts, humano firma. ¿Hay presupuesto asignado o MySEO lo absorbe?
- [CONFIRMAR] **El equipo técnico de Maquiempanadas puede implementar** los fixes de schema, nuevas páginas y hreflang correcto — o MySEO lo hace y factura aparte.
- [CONFIRMAR] **Activar Google Merchant Center con feed de productos** para llegar al bloque "Popular products" (AC6). ¿Existe hoy? Si no, sumar como requisito técnico previo.
- [CONFIRMAR] **El horizonte de 6 meses es aceptable** para la cliente. SEO orgánico no cambia posiciones en 30 días; los 6 nichos duplican el tiempo de asentamiento vs. un mercado único.
- [CONFIRMAR] **Prioridad entre los 6 nichos** — si hay disparidad de tamaño (probablemente Chile >> Costa Rica), ¿la cliente aprueba una asignación de piezas asimétrica en Fase 1 según volumen real que salga de DataForSEO?

### Confirmado implícitamente

- **El SPEC previo** (`SPEC_dataforseo_50usd.md`) queda superado por este; su contenido técnico se absorbe en el proposal.

## Riesgos

### Blast radius
- **Cambios técnicos en el sitio.** Modificar schema, meta tags, structure de URLs puede afectar posicionamiento existente (18 kw pos 1) si se hace mal. Cualquier cambio con impacto en rankings actuales requiere staging + prueba.
- **Contenido con LLM que suene genérico o falso.** Google penaliza contenido sin E-E-A-T real. Si el LLM inventa specs de productos o precios, se destruye credibilidad de marca.
- **Contenido en inglés mal traducido.** Google detecta traducción automática. Contenido en `/en/` debe ser original o profesionalmente traducido.
- **Vocabulario cultural mal usado.** Si una landing "puertorriqueña" usa vocabulario cubano por descuido (o viceversa), el buyer detecta que no es "para él" y el CTR cae. Cada landing nicho requiere revisión por hispanohablante del origen (o al menos alguien familiarizado con el dialecto).
- **hreflang mal configurado.** Con 6 nichos + `/en/`, la matriz hreflang crece. Un solo `hreflang` mal apuntado puede canibalizar rankings entre nichos (Google no sabe cuál mostrar).

### Reversibilidad
- **Fácil:** meta tags, alt-text, schema — se pueden revertir en minutos.
- **Media:** contenido publicado — se puede despublicar pero perder rankings ganados.
- **Difícil:** cambios de URL structure, redirects masivos — comprometen histórico de posicionamiento.

### Dependencias externas
- **DataForSEO** para investigación e histórico de posiciones. Si su API se cae temporalmente, el análisis se pausa.
- **Google Search Console** para medición de resultados. Sus datos tienen delay de 2-3 días.
- **Equipo de Maquiempanadas** para: aprobar contenido, subir cambios técnicos, entregar activos originales (fotos, videos).
- **LLM APIs** (Claude, ChatGPT) para generación asistida — pero el output pasa siempre por revisión humana.

### Impacto en tenants activos
- Solo aplica a Maquiempanadas. No hay impacto cruzado con otros clientes de MySEO.

## Fuera de alcance

Explícitos para prevenir scope creep:

- **Cambios de dominio, migración a HTTPS, cambios de CMS.** El sitio está en Wordpress con WooCommerce — este SPEC trabaja sobre eso, no lo cambia.
- **Google Ads.** Las recomendaciones de Ads se retiraron del reporte cliente. Este SPEC es puramente SEO orgánico. Ads se puede tratar en spec aparte.
- **Redes sociales orgánicas** (Instagram, Facebook, TikTok, LinkedIn). Aunque son parte del ecosistema de contenido, este SPEC se enfoca en Google Search + YouTube Search.
- **Email marketing / lead nurturing.** No se toca el funnel post-clic.
- **Diseño gráfico o UI/UX del sitio.** Cambios visuales quedan a discreción de Maquiempanadas.
- **Traducción a PT del sitio.** `/pt/` existe pero no se invierte en él en este ciclo.
- **Backlinks pagados o schemes de link building agresivos.** Solo outreach orgánico basado en investigación de rivales.
- **Otros idiomas más allá de ES y EN.**
- **México, Argentina, Ecuador y Perú como mercados de foco activo** — quedan como benchmark y como reserva para Fase 2 posterior al ciclo. Se sigue midiendo tráfico pero sin landings dedicadas.
- **Nichos culturales no listados por la cliente** (dominicanos, salvadoreños, hondureños en USA; mexicanos en USA con quesadillas/tamales) — quedan fuera aunque el sitio pueda recibir tráfico de ellos.
- **Creación de nuevos modelos de máquina o moldes específicos por nicho** — los modelos actuales cubren todas las variantes según la cliente. No se pide a Maquiempanadas hacer producto nuevo.
- **Estrategia YouTube contenido nuevo** — se reconecta el canal existente con schema correcto (arreglar los 37 videos con incidencia), pero grabar videos nuevos por nicho queda como iniciativa aparte fuera de este SPEC.


---

# PARTE 3 — Proposal técnico

`03-proposal.md`

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


---

# Preguntas al revisor externo

## Sobre la coherencia del framing

1. La User Story raw pide **rankear**. La cliente aportó luego **6 nichos culturales + 2 mercados profesionales**. El Refined lo traduce en **12+ AC medibles**. El Proposal define **4 fases + 15 piezas de contenido asignadas 1:1 a nichos**. ¿Se sostiene la trazabilidad "user story → nicho → AC → pieza"? ¿Algún nicho queda huérfano sin pieza? ¿Alguna pieza sobra o falta?

## Sobre la estrategia de dos pilares (CM06 + CM5S)

2. Los datos del sitio muestran que **CM5S ya es el mejor posicionado** (pos 4.56 web, #1 en imágenes con 287 clics) y **CM06 es "puerta de entrada" con más clics totales** (225 vs 136). La estrategia acordada es tener dos pilares complementarios en lugar de elegir uno. ¿Es la lectura correcta de los datos? ¿La escalera CM06 → CM5S tiene lógica de funnel comercial o hay riesgo de canibalización SEO?

## Sobre la definición de nichos

3. Los 6 nichos son intersecciones **audiencia × geografía × producto identitario × vocabulario**. ¿Se están tratando como nichos independientes o hay riesgo de que 2-3 converjan en el mismo SERP (ej. cubano en Miami buscando "empanada" puede ver resultados venezolanos también)? ¿Cómo se maneja canibalización entre landings nicho?

## Sobre la meta del feature

4. La meta es **12+ keywords en top 3 distribuidas en 6 nichos + 2 mercados profesionales** en horizonte 6 meses. **15 piezas** como palanca. ¿Es proporcional? ¿15 piezas nicho-específicas alcanzan para mover 12+ keywords a top 3 en 6 mercados distintos?

## Sobre el presupuesto

5. El techo es **$45 USD de DataForSEO**, ahora con Fase 1 subida a $16 (por 8 batches de search_volume) y reserva bajada a $2. ¿Sobra o falta? ¿Los 8 `location_codes` propuestos (2840 USA, 2630 PR, 2188 CR, 2152 CL, 2862 VE, 2724 ES, 2170 COL) son los correctos?

## Sobre los supuestos [CONFIRMAR]

6. Los supuestos ya CONFIRMADOS son 5 (envío internacional, bodegas, producto, dos pilares, adaptación idioma). Quedan 9 [CONFIRMAR] — entre ellos autor firmante, testimonios reales por cada nicho, precios finales, presupuesto redactor humano, Google Merchant Center para AC6. ¿Cuáles son bloqueantes reales para arrancar Fase 1 y cuáles se pueden diferir?

## Sobre riesgos culturales

7. Se agregaron dos riesgos nuevos: **vocabulario cultural mal usado** (una landing "puertorriqueña" con giros cubanos por descuido) y **hreflang mal configurado** entre 6 nichos + `/en/`. ¿Ves algún otro riesgo cultural / lingüístico que no esté cubierto? En particular: ¿cómo se valida que "empanada de pino" (Chile) o "empanadilla" (PR) esté bien usada sin nativo del país?

## Sobre el fuera de alcance

8. Quedan fuera explícitos: dominicanos, salvadoreños, mexicanos en USA (con potencial demanda que el sitio ya recibe según GSC). ¿Es una omisión estratégica correcta o se está dejando dinero sobre la mesa? Nota: el fuera de alcance dice "creación de nuevos moldes" — se supone que la máquina actual hace todas las variantes.

## Sobre la ética y E-E-A-T con 6 nichos

9. Cada landing nicho requiere: foto/video del producto identitario + testimonio real + autor firmante + vocabulario correcto. **Multiplicado por 6 = 6 sets de activos originales**. ¿Ves riesgo de que en la ejecución alguna landing termine cayendo en LLM sin filtro por falta de activos originales de un nicho concreto? ¿La regla "sin activos, no se publica" es sostenible o va a bloquear el sprint?

## Sobre la medición de éxito

10. Los AC3-AC6 miden posición/share/impresiones por categoría universal y por nicho. ¿Falta algún KPI comercial que atara el SEO al negocio (ventas atribuibles por nicho, leads por país, revenue)? `GOAL.md` global es 40 máquinas/mes; el SPEC no compromete un target de máquinas atribuibles. ¿Es correcto separar así, o se debería incluir una atribución mínima (ej. "3 máquinas/mes atribuibles a SEO al t+6m")?

## Sobre el horizonte y ejecución

11. El horizonte es 6 meses para publicar 15 piezas nicho-específicas + medir resultados. Con checkpoints a t+3m y t+6m. ¿Realista? ¿Se debería sub-dividir en 2 sprints (primero los 3 nichos con más volumen probable — típicamente Chile, USA cubano, España — y luego los otros 3)?

## Sobre el Framework aplicado

12. Se usó una adaptación del framework SPECBOOT (originalmente para código) para un proyecto de marketing bajo SEO Harness (nuevo). Los verificadores originales de SPECBOOT (`php artisan test`, `npm lint`) aquí son sustituidos por: Rich Results Test, snapshots de posiciones, checklist de 13 puntos QC. ¿Este trasplante mantiene el rigor? ¿Se pierde alguna disciplina crítica al pasar de código a marketing?
