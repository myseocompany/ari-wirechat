## SPECBOOT — Ciclo SDLC agéntico

Este repositorio adopta SPECBOOT (cortesía LIDR, adaptado) como su ciclo de desarrollo asistido por LLM. Toda feature **no trivial** debe pasar por este ciclo antes de llegar a `main`.

## Flujo

```
USER STORY
  → /enrich_us   → REFINED USER STORY   (specs/active/<slug>/02-refined.md)
  → /propose     → PROPOSAL ARTIFACTS   (specs/active/<slug>/03-proposal.md)
  → /apply       → crea worktree + rama + tests + docs + código base + testing report
     ↺ /verify + /code_review → implement changes  (loop hasta pasar)
  → FEATURE READY
  → /archive + /commit → FEATURE FOR PR → PUBLISHED
```

Cada comando valida el estado previo y avanza el `status` del feature (frontmatter en `01-user-story.md`):

`raw → refined → proposed → applied → ready → archived`

## Cuándo NO aplica (fastpath)

`/fastpath` permite saltarse SPECBOOT solo si **TODAS** estas condiciones son ciertas:

- **<50 líneas** cambiadas
- **Sin cambios de contrato o API pública** (firmas de servicios, endpoints, API Resources, eventos, jobs)
- **Sin nueva dependencia** (`composer.json` y `package.json` intactos)
- **Sin nueva UI visible** (excepción: cambios de copy de ≤1 línea)
- **Sin migración** (`database/migrations/` intacto)

Si cualquiera se cruza → SPECBOOT completo, sin excepciones.

Casos típicos:
- Fix de typo, ajuste de log, mejora de mensaje de error → `/fastpath`
- Bump patch de dependencia → `/fastpath`
- Añadir columna, campo nuevo en formulario, nueva ruta, nuevo evento → **SPECBOOT**
- Refactor de más de 50 líneas → **SPECBOOT** (aunque "no cambie comportamiento")

## Verificadores del repo (usados por `/verify`)

`/verify` corre estos comandos en el orden indicado y solo reporta `PASS` si todos pasan:

1. **Tests dirigidos**: `php artisan test --filter=<slug>` (si el spec declara filtro) o full suite `php artisan test`
2. **Lint frontend**: `npm run lint`
3. **Typecheck frontend**: `npm run typecheck`
4. **Grafo actualizado**: `graphify update .` (solo si existe `graphify-out/graph.json`)

Un feature no sale del loop `/verify + /code_review → implement changes` hasta que los 4 pasen y `/code_review` no arroje observaciones bloqueantes.

## Convenciones

- **Un worktree por feature**. `/apply` crea `git worktree add /private/tmp/<repo>-<slug> -b feature/<slug>` desde `origin/main`.
- **Nombres de rama**: `feature/<slug>` o `fix/<slug>`. Slug en kebab-case, coincidente con el nombre de la carpeta bajo `specs/active/`.
- **Artefactos versionados**: `specs/active/` y `specs/done/` se commitean con el código. Son el changelog conceptual del repo.
- **PR referencia SPEC**: el cuerpo del PR incluye el link a `specs/done/<slug>/03-proposal.md`.
- **Actualizar proposal durante `/apply`**: si la realidad diverge del plan, editar `03-proposal.md` con una sección `## Cambios respecto al plan original`. No descartar el proposal.
- **Testing report obligatorio**: `04-testing-report.md` documenta qué se probó, resultados de cada verificador y decisiones sobre fallos.

## Estructura de artefactos

```
specs/
├── _templates/            ← plantillas base (no editar en features)
│   ├── 01-user-story.md
│   ├── 02-refined.md
│   ├── 03-proposal.md
│   └── 04-testing-report.md
├── active/<slug>/         ← features en vuelo
│   ├── 01-user-story.md   (status: raw|refined|proposed|applied|ready)
│   ├── 02-refined.md
│   ├── 03-proposal.md
│   └── 04-testing-report.md
└── done/<slug>/           ← features publicadas (post /archive)
```

## Interacción con reglas ya vigentes del repo

- **`AGENTS.md`**: SPECBOOT no reemplaza las reglas de `graphify`, disciplina de contexto, publicaciones a Waterfall, UX de Ajustes, ni versionado de prompts. Las complementa.
- **FAQs**: la regla de actualizar `resources/views/public/faq.blade.php` y `docs/FAQ_ARICRM.md` en la misma entrega sigue vigente. Debe reflejarse en los Criterios de Aceptación del `02-refined.md` cuando aplique.
- **Publicaciones a `main`**: el paso `/commit` prepara commit + cuerpo de PR, no hace push a `main` automáticamente. El push a `main` sigue las reglas ya establecidas en `AGENTS.md`.
- **Prompts de agentes IA**: si el feature toca un `system_prompt`, el proposal debe incluir la nueva versión snapshot bajo `docs/<tenant>/prompts/<agente>/v<N>-<descripcion>.md`.

## Comandos disponibles

Definidos globalmente en `~/.claude/commands/`:

| Comando | Rol |
|---|---|
| `/enrich_us` | US cruda → refined (AC, no-func, riesgos, supuestos) |
| `/propose` | Refined → proposal técnico (plan, alternativas, impacto, tests) |
| `/apply` | Crea worktree + rama, scaffolding, inicializa testing report |
| `/verify` | Corre verificadores del repo, valida contra AC |
| `/code_review` | Auto-review estructurado o dispara `/ultrareview` |
| `/archive` | Mueve `specs/active/<slug>/` → `specs/done/<slug>/` |
| `/commit` | Redacta commit + body de PR con referencia al SPEC |
| `/fastpath` | Escape valve para cambios triviales — salta a `/commit` |
