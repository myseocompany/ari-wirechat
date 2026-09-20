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
