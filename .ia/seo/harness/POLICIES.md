# SEO Harness — POLÍTICAS

Reglas duras. Cuando entran en conflicto con otras instrucciones, ganan estas.

## Autónomo (el agente puede hacerlo sin pedir aprobación)

- Ejecutar consultas de DataForSEO **hasta el techo declarado del feature** ($45 USD para `rankear-2026`).
- Generar drafts en Markdown dentro de `.ia/seo/content/<slug>/draft.md`.
- Escribir y actualizar snapshots de monitoreo en `.ia/seo/monitoring/`.
- Cargar y analizar datos de GSC, Ads, YouTube desde `.ia/seo/data/`.
- Ejecutar auditoría técnica on-page (`on_page/*` de DataForSEO) — es investigación, no cambio.
- Actualizar `content_gaps.csv`, `keywords_universe.csv`, `benchmark_categories.md`.
- Correr análisis con LLM (Opus/Sonnet/Haiku) sobre datos ya recolectados.

## Requiere aprobación humana explícita antes de ejecutar

- **Publicar cualquier pieza en `maquiempanadas.com`** (nueva URL, o cambios en URL existente).
- **Modificar structured data en páginas que ya rankean top 3** (riesgo de regresión).
- **Cambiar meta title o meta description** de páginas con >100 clics/mes.
- **Modificar la estructura de URLs** (redirects, renames, delete).
- **Submit al Google Indexing API** — puede tener rate limits y consecuencias si se abusa.
- **Contactar a un dominio externo** para link building (outreach).
- **Publicar cualquier contenido en YouTube** (título, descripción, tags, thumbnail).
- **Superar el techo de gasto DataForSEO** declarado en el proposal.
- **Correr `ai_optimization/llm_responses`** con más de 40 llamadas simultáneas.
- **Compartir informes con la cliente** (Maquiempanadas) — Nicolás revisa primero.

## Prohibido (nunca, sin excepción)

- Leer o compartir credenciales: `.env`, `myseo-env/.env`, tokens de API, contraseñas del CMS, claves privadas.
- Publicar contenido con datos personales de leads o compradores identificables.
- Inventar especificaciones técnicas de productos (dimensiones, capacidades, precios) sin fuente verificable.
- Inventar autor, testimonios, fotos, casos de éxito.
- Copiar contenido de rivales sin transformación sustancial.
- Link building agresivo: PBNs, granjas de enlaces, comment spam, dominios expirados.
- Modificar el CMS o el código del sitio directamente (eso pasa por SPECBOOT en el repo del cliente).
- Correr consultas de DataForSEO más allá del techo sin aprobación.
- Cambiar o borrar un archivo bajo `.ia/seo/specs/done/` (histórico inmutable).

## Trazabilidad obligatoria

Toda pieza publicada debe tener:

1. **Trajectory log** en `content/<slug>/trajectory.md` — qué gap ataca, qué SERP se estudió, qué prompts LLM se usaron, qué modelos, qué revisiones humanas.
2. **Referencia al gap** — línea del `content_gaps.csv` que motivó la pieza.
3. **Autor identificado** — nombre real, cargo, credencial.
4. **Activos originales** — fotos/videos propios de Maquiempanadas o citas verificables.
5. **Rich Results Test aprobado** — screenshot o link a la validación.
6. **Fecha de publicación** en `content_ledger.md`.

Si falta uno, la pieza no se cuenta como cumplida contra los AC del feature.

## Escalación

Si el agente detecta:

- **Sospecha de penalización de Google** (caída súbita >20% en clics de una URL top) → alertar a Nicolás inmediatamente, no publicar más contenido hasta diagnosticar.
- **Rival lanza campaña similar** (aparece rankeando gaps en el mismo momento) → alertar y ajustar prioridades.
- **Cliente hace cambios en el sitio sin avisar** (URLs que dejan de existir, contenido alterado) → alertar y actualizar monitoring.
- **Cuenta DataForSEO se queda sin balance** → pausar research, notificar.
- **LLM devuelve contenido claramente falso** (inventa specs) → descartar draft y ajustar prompt en `prompts/`.
