# Prompts

Plantillas LLM versionadas para cada tipo de contenido. Cada archivo declara modelo objetivo, versión y contexto esperado.

## Archivos actuales

| Prompt | Modelo | Tipo |
|---|---|---|
| `pillar_page.md` | Opus | Outline de pillar page (arquitectura + datos requeridos, no draft completo) |
| `cluster_post.md` | Sonnet | *(pendiente)* Draft de post cluster de 600-1200 palabras |
| `product_faq.md` | Sonnet | *(pendiente)* FAQ para páginas de producto (basado en PAA del SERP) |
| `alt_text_batch.md` | Haiku | *(pendiente)* Alt-text bilingüe para batch de imágenes |
| `local_landing.md` | Sonnet | *(pendiente)* Landing page geo-específica con LocalBusiness schema |
| `video_description.md` | Sonnet | *(pendiente)* Título + descripción + tags + timestamps para video YouTube |
| `meta_batch.md` | Haiku | *(pendiente)* Meta title (≤60c) + description (≤155c) para batch de URLs |

## Cómo usar

1. Cada prompt tiene frontmatter con `prompt`, `version`, `target_model`, `temperature`.
2. Al usar el prompt, se registra en el `trajectory.md` de la pieza: cuál prompt, qué versión, qué modelo, qué costo.
3. Si el output es mediocre → mejorar el prompt e incrementar la versión. Nunca sobrescribir en silencio.

## Versionado

- Cambios menores (aclarar constraint, ajustar tono) → bump minor (v1.0 → v1.1).
- Cambios mayores (nueva regla dura, cambio de estructura de output) → bump major (v1.1 → v2.0).
- En el frontmatter siempre la versión actual.
- Un archivo `prompts/CHANGELOG.md` (pendiente) puede llevar el histórico.
