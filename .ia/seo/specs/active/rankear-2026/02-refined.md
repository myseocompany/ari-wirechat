---
slug: rankear-2026
refined_at: 2026-09-20
last_updated: 2026-09-28 (revisión 4 — 9 mercados analizados con DataForSEO + Informe Anual MQE 2025)
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

## Audiencias objetivo (revisión 4 — 2026-09-28)

La cliente definió el 2026-09-20 seis nichos culturalmente específicos + 2 mercados profesionales cruzados. Tras el batch DataForSEO de 2026-09-28 (`.ia/seo/data/dataforseo/volume_*_es.json`), la matriz se recalibra por volumen real de demanda para el producto core (`maquina * empanadas`, 17 variantes). No son "países" — son intersecciones **audiencia × geografía × producto identitario × vocabulario**:

| Nicho | Audiencia | Reside en | Vol core /mes | Producto identitario | Vocabulario clave | Google | Bodega | Tier |
|---|---|---|---:|---|---|---|---|---|
| N1 | Cubanos | USA (Miami, NJ, NY) | comparte 6,550 USA | Empanada cubana (guayaba y queso, pastelito) | "empanada cubana", "pastelito guayaba" | google.com · ES | USA | **A** |
| N2 | Puertorriqueños | Puerto Rico + USA mainland | 70 en PR / diáspora en USA | **Empanadilla / pastelillo** (no "empanada") | "empanadilla", "pastelillo puertorriqueño" | google.com · ES | USA | **C** — cubrir dentro del contenido latino USA, sin landing dedicada al mercado PR |
| N3 | Costarricenses | Costa Rica | 100 en CR / diáspora en USA | Empanada tica (chiverre, queso) | "empanada tica", "empanada de chiverre" | google.co.cr | Colombia | **C** — cubrir dentro del contenido latino USA, sin landing dedicada |
| N4 | Chilenos | Chile | 1,120 | Empanada de pino (carne + cebolla + aceituna + huevo) | "empanada de pino", "empanada chilena", "fabrica de empanadas" | google.cl | Colombia | **A** — el mayor de los 6 nichos por volumen del core |
| N5 | Venezolanos | Venezuela | 200 | Empanada de harina de maíz amarilla + arepas | "empanada venezolana", "harina PAN", "arepa" | google.co.ve | Colombia | **B** — landing simple; laminadora (590) puede anclar mejor que el core |
| N6 | Colombianos | España (Madrid, Barcelona) | 790 | Empanada colombiana frita (maíz) | "empanadas colombianas Madrid" | google.es · ES | USA o COL | **A** — sin competidor local, alto ROI |
| P1 | Comprador industrial en inglés | USA anglófono | subset de USA en | Cualquier máquina (foco CM5S) | "empanada machine", "commercial empanada machine" | google.com · EN | USA | **B** — landing existente en `/en/` optimizada, sin nueva inversión grande |
| P2 | Empresa / franquicia hispanohablante | LATAM extendido + España | cross-mercado | CM5S multifuncional con telemetría | "máquina industrial empanadas", "línea producción empanadas" | multi | según país | **A** — pilar CM5S sirve a todos los nichos A |

**Legenda de tier:** A = landing dedicada + cluster de contenido; B = landing pero sin cluster satélite; C = vocabulario cubierto en contenido latino USA sin landing propia por bajo volumen. La cliente aprobó adaptación de idioma por nicho el 2026-09-20; la asimetría de esfuerzo por tier se propone aquí en función de datos y requiere ratificación de la cliente antes de ejecutar la Fase 3.

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

## Hallazgos incorporados de la auditoría SEO/GEO/AEO

Fuente: `seo-audit-maquiempanadas-com-2026-09-20.md` (auditoría pública completa, 2026-09-20).

- Base técnica sana: HTTPS, sitemap de Yoast, robots.txt limpio, breadcrumbs y catálogo profundo.
- Bloqueos prioritarios antes de escalar contenido: versiones `/en/` y `/pt/` incompletas y sin `hreflang` verificado; autoría mostrada como email; datos estructurados de producto, receta y video sin confirmar; alt text incompleto; contenido antiguo y páginas de categoría con poco texto útil.
- Activos diferenciales que deben alimentar las piezas: especificaciones verificables, escuelas de Maíz y Trigo, presencia en Colombia y USA, videos propios y certificaciones confirmadas por la empresa.
- Las recomendaciones de schema quedan condicionadas a evidencia real. `aggregateRating`, testimonios, precios, inventario, ofertas y sedes solo se publican cuando exista una fuente autorizada. FAQ puede mejorar comprensión y AEO, pero no se promete un rich result general de Google.

## Hallazgos incorporados del Informe Anual MQE 2025

Fuente: `MQE_Informe Anual_2025.pdf` (interna Maquiempanadas, enero 2025), revisada 2026-09-28.

- **El cuello de botella del negocio no es tráfico — es calidad de lead.** El funnel 2022-2025 muestra: 2022 → 11.833 prospectos / 2.81% conv; 2024 → 31.762 prospectos / **0.34% conv**; 2025 → 22.703 prospectos / **1.31% conv**. En 2024 duplicaron el volumen y las ventas cayeron (333 → 310). En 2025 redujeron 28% el volumen y la conversión calificado→venta se cuadruplicó (4.9% → 15.3%). **Cualquier iniciativa SEO se mide por leads calificados y ventas, no por tráfico bruto.**
- **Ventas cerradas se mantienen estables ~300/año independientemente del volumen de prospectos** (2018-2025 rango 195-333). Meta comercial de 40 máquinas/mes (`GOAL.md`) implica sostener ~480/año — 60% arriba del máximo histórico. SEO orgánico solo puede aportar a esa meta si genera leads que califican, no leads que inflan el funnel.
- **Share global de búsquedas de "maquina de hacer empanadas" según el informe:** Colombia 17.8%, USA 16.0%, Argentina 14.0%, España 10.1%, México 9.4%. Estos porcentajes se cruzan más abajo con volumen real por país medido en DataForSEO.
- **Marca "maquiempanadas" en declive** — búsqueda de marca lleva 3 años bajando; tráfico web -12% activos, -18% usuarios nuevos. La estrategia SEO offensiva no aborda esta erosión directamente.
- **Presencia social del sector:** Maqui lidera Instagram (81.8K seguidores) pero con engagement 0.02% (peor del sector), muy por detrás de Lovermaq (0.87%) y Metalurgica Vasquez (1.54%). La audiencia existe pero está desconectada del contenido. Fuera de scope de este SPEC.

## Hallazgos DataForSEO — 9 mercados analizados (2026-09-28)

Fuente: batch DataForSEO sobre las 30 keywords semilla, `location_code` propio de cada mercado. JSON crudo en `.ia/seo/data/dataforseo/volume_<mkt>_es.json` y `rank_overview_maquiempanadas_com_<mkt>.json`. Detalle interactivo en `reporte_maquiempanadas.html` sección "Nichos culturales LATAM".

**Lectura por cluster de producto — no por keyword individual.** El cluster `maquina * empanadas` se fragmenta en 17 variantes cuya suma es lo que representa la demanda real del producto core (CM06/CM5S). Reportar solo la keyword individual más grande sesga el análisis hacia productos adyacentes como laminadora de masa, que concentra su volumen en 1-2 términos.

**Volumen agregado por cluster de producto (búsquedas/mes):**

| Cluster | USA | COL | AR | MX | CL | ESP | VE | CR | PR |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| **`maquina * empanadas` (core, 17 vars)** | **6,550** | **1,930** | **1,580** | **1,360** | **1,120** | **790** | 200 | 100 | 70 |
| `molde/prensa empanadas` | 1,040 | 1,290 | 840 | 1,070 | 960 | 820 | 110 | 100 | 60 |
| `arepa/máquina arepas` | 540 | 780 | 50 | 70 | 180 | 510 | 70 | 20 | 50 |
| `laminadora de masa` (adyacente) | 790 | 1,310 | 1,010 | **3,610** | 1,310 | 890 | 590 | 50 | 20 |
| `fabrica empanadas / desmechadora` | 110 | 960 | 1,620 | 150 | 930 | 140 | 80 | 30 | 20 |

**Solo la keyword `maquina(s) para hacer empanadas` pura (2 variantes):** USA 960 · COL 960 · AR 780 · MX 780 · CL 640 · ESP 340 · VE 60.

**Priorización real por mercado — recalibrada por cluster core:**

| # | Mercado | MAQ_EMPANADA (core) | Maqui #1 | Priorización |
|---|---|---:|---:|---|
| 1 | USA (en+es) | 6,550 | 18 | **defensivo prioritario** — mercado core más grande, ya lideramos con 18 kw en pos #1 |
| 2 | Colombia | 1,930 | 10 | **defensivo** — segundo mercado más grande del producto core |
| 3 | Argentina | 1,580 | 0 | **documentado, NO propuesto** (ver Fuera de alcance) |
| 4 | México | 1,360 | 0 | **no-priorizar** para el core — zonachef+foxsteel dominan y su fuerte es laminadora (3,610), no `maquina para empanadas` |
| 5 | Chile | 1,120 | 0 | **PRIORIDAD ALTA** — el mayor de los 6 nichos culturales originales para el producto core |
| 6 | España | 790 | 0 | **PRIORIDAD ALTA** — cero competidor local, apoyo con cluster laminadora (890) que empata |
| 7 | Venezuela | 200 | 0 | marginal para el core, más viable si se ataca laminadora (590) |
| 8 | Costa Rica | 100 | 0 | **volumen insuficiente** para landing SEO dedicada |
| 9 | Puerto Rico | 70 | 0 | **volumen insuficiente** para landing SEO dedicada |

**USA nota:** el total de 9,060 del batch inicial estaba inflado por `empanada maker` (2,400) y `empanada press` (1,600) — consumer keywords que rankean retail masivo (Amazon, Target). El cluster real de comprador industrial en USA es de 6,550/mes.

**Cambios que esto introduce al SPEC:**

1. **La estrategia de dos pilares de producto se refuerza, no se sustituye.** Los pilares CM06 y CM5S siguen siendo el corazón del plan — el cluster `maquina * empanadas` con 6,550-1,120 búsquedas/mes en 5 mercados lo justifica plenamente.
2. **Laminadora se degrada de "gap universal" a "categoría adyacente de refuerzo".** Se puede tratar como cluster satélite, útil especialmente en México (3,610/mes, único mercado donde supera al cluster core), pero no como ancla estratégica. Si Maqui decide priorizar comercialmente laminadora, es una decisión de producto, no de SEO.
3. **Chile promocionado dentro de los nichos originales** — 1,120/mes en `maquina * empanadas` + 930 en `fabrica/desmechadora` + 1,310 en laminadora. Perfil similar a Argentina (B2B fuerte con `fabrica de empanadas`).
4. **Costa Rica y Puerto Rico degradados** — el cluster core cae a 100 y 70/mes respectivamente. Ruido estadístico. La audiencia cultural probablemente vive en la diáspora (USA/España), no en el país. El vocabulario (`empanadilla`, `pastelillo`, `empanada tica`) puede cubrirse en contenido latino en USA sin landing dedicada.
5. **Venezuela reducido a marginal** — cluster core en 200/mes; laminadora (590) es lo único con volumen razonable. Landing simple si se hace, sin cluster completo.
6. **Argentina se documenta como observación**, no se propone (ver Fuera de alcance actualizado).
7. **México se ratifica como no-priorizado para el core**. Sí puede evaluarse aparte una entrada al segmento laminadora si es estratégico, pero no dentro de este SPEC.

**Competidores identificados vía DataForSEO (no en versiones previas del SPEC):**

- USA (en): `ankofood.com` (145 kws, único rival serio)
- Colombia: `adlovermaquinas.com` (20 kws), `gruenn.com.co` (39 kws), `ingeneumatica.com` (42 kws)
- Argentina: `industriaromano.com.ar` (91 kws, principal), `monv.com.ar` (35 kws, Metalurgica Vasquez), `empatec.com.ar` (12 kws)
- México: `zonachef.com.mx` (3,040 kws), `foxsteel.com.mx` (1,531 kws) — dominantes en laminadora y equipo comercial industrial hispano
- Brasil: `bralyx.com` (401 kws, líder — no compite en Google hispanoparlante)
- Sin competidor SEO hispano consolidado: España, Puerto Rico, Costa Rica, Venezuela

## Criterios de aceptación

Formato Gherkin. Cada AC verificable programáticamente contra datos de DataForSEO / GSC.

### Categoría: cobertura de keywords rankeando

- **AC1 — Universo de keywords útil para decidir.** Given las semillas de Maquiempanadas, GSC, Ads y los rivales observados en cada SERP, When se ejecuta la investigación por nicho, Then existe un CSV `keywords_universe.csv` deduplicado para USA-ES, Puerto Rico, Costa Rica, Chile, Venezuela, España, USA-EN y Colombia, con nicho, idioma, intención, volumen cuando esté disponible, dificultad, SERP dominante, producto aplicable y presencia actual. No existe una cuota mínima de filas: una keyword solo entra si puede cambiar una decisión de contenido, página o mercado.

- **AC2 — Gaps priorizados por valor.** Given el universo de AC1, When se contrasta con competidores directos y la intención observada en la SERP, Then existe `content_gaps.csv` con demanda, intención comercial, ajuste producto/mercado, valor potencial, probabilidad de rankear, esfuerzo y fuente. La prioridad se calcula como `(demanda × intención × ajuste × valor × probabilidad_de_rankear) / esfuerzo`; la posición rival nunca se multiplica de forma directa. No existe una cuota mínima de gaps.

### Categoría: rankings objetivo por país + categoría universal

- **AC3 — Meta de posición web (texto) por tier de nicho.** Al terminar el ciclo (horizonte 6 meses tras publicar contenidos), Maquiempanadas debe estar en **top 3 orgánico** en las siguientes keywords, distribuidas por tier de nicho (tier definido en "Audiencias objetivo" por volumen real del cluster core):
  - **Tier A** (N1 Cubanos, N4 Chilenos, N6 Colombianos en España + P2 profesional cross-mercado): 2 keywords comerciales en top 3 por nicho + 2 del cluster profesional. **Total: 10 keywords.**
    - N1: `maquina para hacer empanadas cubanas`, `maquina pastelitos cubanos`.
    - N4: `maquina empanadas chilenas`, `maquina empanadas de pino` (opcional: `fabrica de empanadas chile`).
    - N6: `maquina empanadas colombianas madrid`, `venta maquina empanadas españa`.
    - P2: `linea produccion empanadas industrial`, `maquina empanadas semiautomatica`.
  - **Tier B** (N5 Venezolanos + P1 anglófono): 1 keyword comercial en top 3 por nicho. **Total: 2 keywords.**
    - N5: `laminadora de masa venezuela` o `maquina empanadas venezolanas` (la de mejor SERP tras validar).
    - P1: `commercial empanada machine` o `automatic empanada machine`.
  - **Tier C** (N2 Puertorriqueños, N3 Costarricenses): **sin meta de top 3 directa por bajo volumen local (100 y 70/mes en cluster core).** El vocabulario se cubre dentro del contenido latino USA (N1) para capturar la diáspora sin crear landings dedicadas a los mercados PR y CR. Meta indirecta: que la landing latina USA capture al menos 1 keyword `empanadilla` o `pastelillo` en top 5 (validar tras Fase 1).
  
  **Total meta explícita: 12 keywords en top 3** (10 Tier A + 2 Tier B). Las 18 keywords en posición #1 ya existentes se mantienen — meta de defensa: **no perder ninguna**. Medición: GSC como fuente primaria y snapshots reproducibles de `serp/google/organic/live/advanced` o del flujo Standard `task_post` + `task_get/advanced` en t0, t+3m y t+6m. `historical_serps` puede complementar, pero no sustituye la línea base propia. Keywords finales se cierran en Fase 1 con demanda, intención, competencia y ajuste comercial verificados.

- **AC4 — Meta de imágenes por nicho.** El cluster comercial en Google Images (queries con producto identitario de cada nicho + `máquina` / `molde` / `arepa`) debe mover su posición promedio ponderada por impresiones a **≤10 en al menos 4 de los 6 nichos** al final del ciclo. Prioridad de auditoría de imágenes: CM5S (activo #1 hoy) + CM06 + CM06B con alt-text nicho-específico ("máquina para hacer empanadillas puertorriqueñas" ≠ "máquina para hacer empanada de pino"). Medición: GSC `search type = image` + Países + DataForSEO `serp/google/images/live/advanced`.

- **AC5 — Meta de video.** Los videos indexados del sitio deben pasar de **33 impresiones/16m a ≥1,000 impresiones/3m** en Google Search (categoría video) — reflejando que el canal YouTube (1.18M views/año) empieza a impactar el SEO web. Medición: GSC `search type = video`.

- **AC6 — Meta de Shopping / popular products.** En al menos 3 keywords cabeza (`maquina para hacer empanadas`, `empanada machine`, `maquina para hacer arepas`), un producto de Maquiempanadas debe aparecer en el bloque "Popular products" de Google. Hoy: 0. Requiere activar Google Merchant Center con feed de productos correctamente estructurado (CM06 y CM5S como prioridad). Medición: `serp/google/organic/live/advanced` (feature `popular_products`).

### Categoría: contenido publicado

- **AC7 — Content plan ejecutado por cohortes.** Las 15 piezas son un backlog máximo, no una cuota. Primero se corrigen los bloqueos técnicos confirmados por la auditoría y se publica una cohorte piloto de **3-5 piezas**. Las siguientes se liberan cuando cada nicho demuestre demanda comercial, SERP abordable, logística viable, activos originales disponibles y una señal de avance a los 30-90 días. Backlog candidato:
  - **2 pillar pages master:** CM06 (empanadas + arepas, puerta de entrada) y CM5S (multifuncional profesional). En español base.
  - **6 landing nicho:** una por audiencia cultural (N1-N6). Cada una con vocabulario, foto/video del producto identitario, testimonio local, LocalBusiness schema apuntando a la bodega que envía.
  - **6 posts cluster:** uno por nicho, tipo "cómo empezar negocio de empanadas [tipo] en [ciudad]", enlazando al pilar y a la landing nicho correspondiente.
  - **1 pillar en inglés** (CM5S) para P1 anglófono industrial.
  Cada pieza con: alt-text descriptivo en idioma/dialecto del nicho, structured data válido y sustentado (Product / Video / Recipe / FAQ / LocalBusiness según aplique), imagen original o video embed, tabla de datos, autor identificado y `hreflang` correcto cuando exista una variante regional real. Las landings de mercado no se marcan como `LocalBusiness` salvo que representen una sede física real.

- **AC8 — Video schema corregido.** Los 37 videos con incidencia "no está en página de visualización" deben pasar auditoría de Rich Results Test de Google. Medición: reporte GSC de Video Indexing.

- **AC9 — Presencia en LLMs medida y con línea base reproducible.** Existe `llm_presence_baseline.md` con 5-8 queries comerciales congeladas y los mismos modelos, configuración y fecha en cada medición, mostrando: (a) si Maquiempanadas es citada, (b) qué dominios son citados y (c) evidencia de la respuesta. Baseline t0 antes del piloto; remedición a t+3m y t+6m. Este indicador es exploratorio y no reemplaza oportunidades o ventas.

### Categoría: proceso y trazabilidad

- **AC10 — Cada consulta DataForSEO deja rastro.** JSON crudo en `.ia/seo/data/dataforseo/YYYY-MM-DD_<endpoint>_<slug>.json` + fila en `.ia/seo/data/dataforseo/COSTS.csv` con costo real.

- **AC11 — Presupuesto DataForSEO operado por compuertas.** El gasto acumulado del primer ciclo, incluido el gasto ya registrado, no supera **USD 15,50**. Cada batch declara endpoint, número de tareas, límite/depth, tarifa estimada, costo máximo y decisión que habilita. Superar ese techo requiere una decisión documentada; el saldo se conserva para validaciones y monitoreo posteriores.

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

- **Métricas comerciales:** esta iniciativa es de **adquisición** y actúa principalmente entre educación, priorización y compromiso mutuo. Se reportan como máximo tres métricas ejecutivas: oportunidades orgánicas calificadas, conversión de oportunidad orgánica a venta, e ingreso cobrado atribuible o asistido por orgánico. Rankings, impresiones y menciones son señales, no ventas.

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
- **Argentina como mercado objetivo** — el batch DataForSEO del 2026-09-28 reveló que Argentina tiene **1,580/mes en el cluster `maquina * empanadas`** (más que México, Chile y España) + 1,620/mes en `fabrica de empanadas / desmechadora` — es decir, un mercado B2B robusto con competencia local débil (`industriaromano.com.ar` top con 91 kws). El Informe Anual MQE 2025 también lo posiciona en 14% del share global. **Pese a los datos, no se propone como mercado objetivo en este ciclo:** la cliente no lo incluyó en su lista de audiencias del 2026-09-20, y probablemente existen razones logísticas o comerciales (envío, soporte técnico, aranceles) para esa exclusión. Los datos quedan **documentados** en `reporte_maquiempanadas.html` sección "AR + MX" y en los JSON de `.ia/seo/data/dataforseo/*_ar_*.json` para consulta futura, pero el SPEC no crea landing, pilar ni cluster para Argentina.
- **México como mercado objetivo activo** — se ratifica como no-priorizado. La demanda en el cluster core es real (1,360/mes) y en laminadora es enorme (3,610/mes), pero los rivales locales `zonachef.com.mx` (3,040 kws, ETV $9,048/mes) y `foxsteel.com.mx` (1,531 kws, ETV $5,744/mes) son 30× mayores que el rival principal en Argentina. Entrada a México requiere presupuesto y estrategia aparte, fuera de este ciclo.
- **Ecuador y Perú como mercados de foco activo** — quedan como benchmark y como reserva para ciclos futuros. Se sigue midiendo tráfico pero sin landings dedicadas.
- **Nichos culturales no listados por la cliente** (dominicanos, salvadoreños, hondureños en USA; mexicanos en USA con quesadillas/tamales) — quedan fuera aunque el sitio pueda recibir tráfico de ellos.
- **Landings dedicadas para Puerto Rico y Costa Rica** — reclasificados a Tier C tras el batch DataForSEO (volumen cluster core 70 y 100/mes respectivamente). El vocabulario (`empanadilla`, `pastelillo`, `empanada tica`) se cubre dentro del contenido latino USA (N1) sin crear páginas dedicadas para esos mercados. Se sigue enviando producto a ambos, pero SEO no crea infraestructura de contenido para ellos.
- **Landing/cluster dedicado a "laminadora de masa" como categoría propia** — es una categoría adyacente con volumen real (3,610/mes en México, 1,310 en COL/CL) pero no es el producto core de Maquiempanadas. Si Maqui decide priorizar comercialmente laminadora como línea, se aborda en spec aparte. En este SPEC, laminadora aparece solo como cluster satélite que refuerza a los pilares de empanada.
- **Creación de nuevos modelos de máquina o moldes específicos por nicho** — los modelos actuales cubren todas las variantes según la cliente. No se pide a Maquiempanadas hacer producto nuevo.
- **Estrategia YouTube contenido nuevo** — se reconecta el canal existente con schema correcto (arreglar los 37 videos con incidencia), pero grabar videos nuevos por nicho queda como iniciativa aparte fuera de este SPEC.
