# SEO Harness — MySEO × Maquiempanadas

Harness de trabajo SEO agéntico. Adaptado del harness MYSEO (`/Volumes/ExternoMS/projects/myseo-env/`) pero especializado en SEO: investigación con DataForSEO, generación de contenidos con LLM, publicación con revisión humana obligatoria, y medición sostenida por país + categoría universal (texto, imágenes, video, shopping).

## Cuándo aplicar

- Cualquier trabajo de posicionamiento orgánico en `maquiempanadas.com` o el canal YouTube asociado.
- Cualquier decisión que afecte el contenido publicado, la estructura de URLs, el schema, o la relación entre el sitio y sus rivales orgánicos.

## Cuándo NO aplicar

- Cambios de código de la aplicación Laravel (usar SPECBOOT en `/specs/`).
- Marketing off-SEO (Meta Ads, email, redes orgánicas) — usar MYSEO base.
- Google Ads — usar MYSEO base con ciclo SCAN → CONFIGURE → INSTALL → LAUNCH → MONITOR.

## Método

```
RESEARCH → GAP_ANALYSIS → CONTENT_PLAN → GENERATE → PUBLISH → MONITOR → EVOLVE
```

Cada paso está descrito en `workflows/`. El agente sigue el método sin saltarse pasos.

## Jerarquía de reglas

Cuando hay conflicto, gana el archivo más alto:

1. `POLICIES.md` — reglas duras (qué requiere aprobación humana, qué está prohibido)
2. `SOUL.md` — rol y principios del agente SEO
3. `METHOD.md` — método de trabajo
4. `skills/` — reglas operativas por dominio (QC, routing, hygiene)
5. `workflows/` — pasos concretos
6. `prompts/` — plantillas LLM
7. `templates/` — plantillas de output

## Archivos operativos

Los archivos vivos del proyecto (fuera del harness):

- `../analisis_keywords_gsc.md` — diagnóstico
- `../data/` — datos crudos (GSC, DataForSEO, Ads, YouTube)
- `../specs/active/rankear-2026/` — feature en vuelo (formato MYSEO)
- `../content/YYYY-MM-DD_slug/` — piezas publicadas con trajectory log
- `../monitoring/YYYY-WW/` — snapshots semanales de posiciones

## Comandos disponibles

- `/enrich_us`, `/propose`, `/apply`, `/verify`, `/code_review`, `/archive`, `/commit`, `/fastpath` — heredados de SPECBOOT (global en `~/.claude/commands/`). El harness SEO adapta el uso a features no-código: `/apply` no crea worktree ni tests unitarios sino que scaffoldea contenido; `/verify` corre chequeos de contenido (Rich Results Test, alt-text presente, links funcionales).
