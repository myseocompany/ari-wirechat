# Workflow — GENERATE

## Objetivo

Producir drafts de calidad E-E-A-T con LLM asistido y checklist QC de 13 puntos aprobado.

## Pasos por pieza

1. **Abrir el trajectory.**
   - `cp harness/templates/TRAJECTORY.md content/YYYY-MM-DD_<slug>/trajectory.md`.
   - Rellenar campos 1 (motivación) y 2 (SERP objetivo).

2. **Snapshot del SERP objetivo.**
   - `serp/google/organic/live/advanced` para la keyword del gap.
   - Guardar JSON en `data/dataforseo/YYYY-MM-DD_serp_<slug>.json`.
   - Extraer: top 5 orgánicos, PAA, imágenes ranking, videos ranking, popular_products.

3. **Consultar benchmark cross-category.**
   - Buscar en `benchmark_categories.md` el patrón del nicho análogo.
   - Copiar la sección relevante al trajectory (paso 3).

4. **Elegir prompt template.**
   - Según tipo de pieza: `prompts/pillar_page.md`, `prompts/cluster_post.md`, `prompts/product_faq.md`, etc.
   - Verificar versión del prompt (frontmatter).

5. **Elegir modelo según SKILL_model_routing.**
   - Pillar page outline: Opus.
   - Draft de cluster post: Sonnet.
   - Alt-text batch, meta descriptions, FAQ items: Haiku.

6. **Preparar input al LLM.**
   Contexto obligatorio pasado al prompt:
   - Snapshot del SERP objetivo (extractos, no JSON entero).
   - Patrón del benchmark análogo.
   - Ficha oficial del producto o entidad protagonista.
   - Constraints: idioma, longitud, tono, structured data requerido.
   - Referencia explícita a `SKILL_content_qc.md`.

7. **Correr generación.**
   - LLM produce draft en `content/YYYY-MM-DD_<slug>/draft.md`.
   - Registrar en trajectory paso 4: modelo, prompt, tokens, costo.

8. **Auto-checklist QC.**
   - El agente lee el draft y marca cada punto del checklist 13 con evidencia.
   - Si algún punto está `[?]` o `[ ]`, iterar.

9. **Assets fetch.**
   - Localizar fotos/videos de Maquiempanadas para embebbeer.
   - Escribir alt-text con Haiku en batch.
   - Preparar structured data JSON-LD.

10. **Rich Results Test.**
    - Validar todo JSON-LD con https://search.google.com/test/rich-results.
    - Guardar screenshot o link de validación en trajectory paso 5.8.

11. **Enviar a revisión humana.**
    - Estado en `content_ledger.md` pasa a `review`.
    - Notificar a Nicolás.

## Iteración

Cada vez que Nicolás pide cambios:
- Registrar feedback en trajectory paso 6.
- Iterar el draft (nueva sección, no sobrescribir).
- Re-verificar checklist completo.
- No pasar a `PUBLISH` hasta tener aprobación explícita.

## Techo de gasto en este paso

**$3-5 USD por pieza** en LLM (Opus + Sonnet + Haiku combinados). Cuenta contra el techo total del feature.

## No hacer

- No publicar sin `[✓]` en los 13 checkpoints.
- No pedir a Opus lo que Haiku puede hacer.
- No inventar activos si no existen — se detiene la pieza y se pide a Maquiempanadas.
- No copiar párrafos de rivales. Transformación sustancial mínima.
