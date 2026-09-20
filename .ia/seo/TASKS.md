# SEO Tasks — Maquiempanadas

## Bloqueantes (resolver antes de gastar créditos DataForSEO)

- [x] **B1** — ~~Verificación DataForSEO~~ (resuelto — cuenta verificada por link de email, endpoints de pago funcionando. Batch inicial ejecutado con costo $0.38 USD)
- [x] **B2** — ~~Acceso al panel de Google Ads~~ (resuelto — search terms report + auction insights en `data/google_ads/`)
- [ ] **B2.1** — Confirmar con la cliente **qué evento cuenta como "conversión"** en Ads (CPA $2 USD sugiere que es lead/WhatsApp, no venta)
- [ ] **B2.2** — Auditoría manual de `ferrero-machines.com` (competidor #1 en subasta) y `adlovermaquinas.com` (#2)
- [ ] **B2.3** — Pausar/rehacer campaña `[Search] - Llamadas` (gastó $148K COP con 0 conversiones)
- [ ] **B2.4** — Excluir términos en inglés irrelevantes de PMax (food industry machines, food production machines, etc.)
- [x] **B3** — ~~Cruce País × Página × Consulta~~ (resuelto con exports segmentados USA/COL × Web/Imagen/Vídeo en `data/gsc/countries/`)
- [ ] **B4** — Data del CRM: tasas de conversión de tráfico orgánico y Ads
- [x] **B5** — ~~Re-exportar Páginas.csv~~ (cerrado como imposible — delta bidireccional confirma que es comportamiento intrínseco de GSC, no filtro. Ver análisis v7)
- [ ] **B6** — Confirmar con la cliente: envío a USA, catálogo real vendible a USA, aporte esperado de SEO al 40/mes

## Fase 1 — Candidatas de optimización (post bloqueantes)

- [ ] **T1** — Arreglar 36 videos con incidencia "no está en página de visualización" (schema VideoObject) — desbloquea el tipo de búsqueda Video (hoy 33 impresiones/16m)
- [ ] **T2** — Auditoría on-page de CM06 y CM06B
- [ ] **T3** — Auditoría on-page de `/en/` y productos en inglés (confirmar hipótesis con Ads data)
- [ ] **T3.5** — Auditoría de imágenes (alt-text, filenames, structured data, tamaños). Imagen es 24% del share con pos 21-36; USA en pos 36.54

## Fase 2 — Investigación DataForSEO (post B1)

- [x] **T4** — ~~Volumen de búsqueda~~ (ejecutado: 30 kw × USA-es/en + COL, en `data/dataforseo/volume_*.json`)
- [x] **T5** — ~~SERP orgánico~~ (ejecutado para 5 keywords principales en `data/dataforseo/serp_*.json`)
- [x] **T6** — ~~Análisis competidores~~ (ejecutado: Anko + Ferrero + Maquiempanadas — Anko es el rival real, no Ferrero)
- [ ] **T6.1** — Ampliar análisis con `dataforseo_labs/google/serp_competitors/live` (competidores agregados por keyword set)
- [ ] **T6.2** — Traducir hallazgos v8 a plan concreto: qué páginas optimizar primero

## Fase 3 — Análisis extendido

- [ ] **T7** — Presencia en LLMs (ChatGPT/Claude/Gemini/Perplexity) — endpoint AI Optimization
- [ ] **T8** — Baseline dominio (ranked keywords, backlinks)
- [x] **T9** — ~~Integrar datos de YouTube~~ (integrado en v7 sección 4.3: 3.77M views totales, 1.29M último año, mismas keywords cabeza que Google, distribución geográfica coherente CO/USA/ES)
- [ ] **T9.1** — Diagnosticar el salto de 6x en views 2024→2025 (¿cambio estratégico intencional o viral?)
- [ ] **T9.2** — Diseñar estrategia para reconectar canal YouTube (1.29M views/año) con SEO del sitio vía markup de video correcto

## Completado

- [x] Recopilar exports GSC del sitio (3m, 16m, países, páginas, dispositivos, AI features, video indexing, coverage)
- [x] Análisis exploratorio con reglas reproducibles (`analisis_keywords_gsc.md` v6)
- [x] Descubrir estructura multilenguaje del sitio (`/en/`, `/pt/`) y catálogo real
- [x] Detectar campaña de Google Ads activa (`23069071844`)
- [x] Mapear endpoints DataForSEO priorizados por pregunta (`tools/endpoints_priorizados.md`)
