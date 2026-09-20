---
slug: rankear-2026
refined_at: 2026-09-20
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

## Criterios de aceptación

Formato Gherkin. Cada AC verificable programáticamente contra datos de DataForSEO / GSC.

### Categoría: cobertura de keywords rankeando

- **AC1 — Universo de keywords mapeado.** Given las semillas de Maquiempanadas y sus rivales, When se ejecuta la investigación de DataForSEO Labs, Then existe un CSV `keywords_universe.csv` con **≥500 keywords** clasificadas por país (USA/COL/MEX/ARG/ESP), idioma (ES/EN), intent (info/comm/trans/nav), volumen, dificultad y presencia actual de Maquiempanadas.

- **AC2 — Content gaps identificados.** Given el universo de AC1, When se cruza con las keywords donde `ankofood.com`, `adlovermaquinas.com` y `ferrero-machines.com` rankean top 10 y Maquiempanadas no aparece en top 20, Then existe `content_gaps.csv` con **≥100 gaps** priorizados por (volumen × posición_rival).

### Categoría: rankings objetivo por país + categoría universal

- **AC3 — Meta de posición web (texto).** Al terminar el ciclo (horizonte 6 meses tras publicar contenidos), Maquiempanadas debe estar en **top 3 orgánico** para al menos 10 keywords comerciales adicionales a las actuales, distribuidas así:
  - USA español: 5 keywords en top 3 (además de las 18 en pos 1 ya existentes).
  - Colombia: 3 keywords en top 3.
  - Otro país LATAM (MEX o ARG): 2 keywords en top 3.
  Medición: `dataforseo_labs/google/historical_serps/live` comparando t0 vs t+6m.

- **AC4 — Meta de imágenes.** El cluster comercial en Google Images (queries con `máquina`, `molde`, `arepa`, `desmechadora` + producto) debe mover su posición promedio ponderada por impresiones **de 20-36 a ≤10** en USA y a **≤6** en Colombia. Medición: GSC filtrado por `search type = image` + Países.

- **AC5 — Meta de video.** Los videos indexados del sitio deben pasar de **33 impresiones/16m a ≥1,000 impresiones/3m** en Google Search (categoría video) — reflejando que el canal YouTube (1.18M views/año) empieza a impactar el SEO web. Medición: GSC `search type = video`.

- **AC6 — Meta de Shopping / popular products.** En al menos 3 keywords cabeza (`maquina para hacer empanadas`, `empanada machine`, `maquina para hacer arepas`), un producto de Maquiempanadas debe aparecer en el bloque "Popular products" de Google. Hoy: 0. Medición: `serp/google/organic/live/advanced` (feature `popular_products`).

### Categoría: contenido publicado

- **AC7 — Content plan ejecutado.** Deben publicarse en `maquiempanadas.com` **≥15 nuevas piezas de contenido** de acuerdo al plan (pillar pages, posts de cluster, páginas de producto ampliadas, casos de uso locales). Cada una con: alt-text descriptivo bilingüe, structured data válido, imagen original o video embed, tabla de datos o especificación.

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

Todo marcado con `[CONFIRMAR]` antes de `/propose`.

- [CONFIRMAR] **Maquiempanadas puede producir contenido audiovisual original** (fotos, videos de sus máquinas operando) al ritmo requerido por el plan — no todo puede generarse con LLM.
- [CONFIRMAR] **Existe un autor humano identificable** en el equipo de Maquiempanadas que pueda firmar los posts para cumplir E-E-A-T (nombre real, cargo, credenciales).
- [CONFIRMAR] **El equipo técnico de Maquiempanadas** puede implementar los fixes de schema y las nuevas páginas — o MySEO lo hace y factura aparte.
- [CONFIRMAR] **Presupuesto para creación de contenido está definido** — LLM ayuda pero no reemplaza redactor humano final. ¿Hay presupuesto para editor bilingüe?
- [CONFIRMAR] **El horizonte de 6 meses es aceptable** para el cliente. SEO orgánico no cambia posiciones en 30 días.
- [CONFIRMAR] **Colombia es prioridad de defensa, USA de crecimiento**. Otros países (MEX, ARG, ESP) van en fase 2.
- [CONFIRMAR] **El SPEC previo** (`SPEC_dataforseo_50usd.md`) queda superado por este; su contenido técnico se absorbe en el proposal.

## Riesgos

### Blast radius
- **Cambios técnicos en el sitio.** Modificar schema, meta tags, structure de URLs puede afectar posicionamiento existente (18 kw pos 1) si se hace mal. Cualquier cambio con impacto en rankings actuales requiere staging + prueba.
- **Contenido con LLM que suene genérico o falso.** Google penaliza contenido sin E-E-A-T real. Si el LLM inventa specs de productos o precios, se destruye credibilidad de marca.
- **Contenido en inglés mal traducido.** Google detecta traducción automática. Contenido en `/en/` debe ser original o profesionalmente traducido.

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
- **España, México, Argentina y Ecuador** como mercados de foco activo — quedan como benchmark de datos, no de contenido dedicado en este ciclo.
