---
skill: model_routing
version: 1.0
---

# SKILL — Model Routing (Opus / Sonnet / Haiku)

Regla operativa para elegir modelo según trabajo. Aplica costo y capacidad al output esperado.

## Regla general

| Modelo | Costo relativo | Cuándo usar |
|---|---:|---|
| **Opus** | 5x | Pensamiento estratégico, decisiones con impacto de alcance, revisión crítica |
| **Sonnet** | 1x | Drafts intermedios, adaptación de tono, síntesis balanceada |
| **Haiku** | 0.2x | Volumen alto con patrón repetitivo, output acotado en formato |

## Tabla por tarea

| Tarea SEO | Modelo | Razón |
|---|---|---|
| Analizar 100 SERPs de benchmark cross-category y extraer patrones | Opus | Pensamiento comparativo profundo |
| Outline de pillar page (H1-H6, secciones, angle) | Opus | Decide arquitectura del contenido, alto valor |
| Interpretación de gap analysis (¿por qué Anko gana esta keyword?) | Opus | Requiere hipótesis y razonamiento |
| Revisión final de draft antes de publicar | Opus | Último filtro, no puede fallar |
| Ideación de topic clusters | Opus | Estratégico |
| Draft inicial de post cluster (600-1200 palabras) | Sonnet | Balance costo/calidad |
| Adaptación de un post ES a EN (con transformación, no traducción) | Sonnet | Requiere contexto cultural |
| Síntesis de PAA en una FAQ coherente | Sonnet | Formato semi-libre |
| Escribir descripción de video YouTube (200-500 palabras con timestamps) | Sonnet | Formato medio |
| Alt-text batch (300+ imágenes, 5-15 palabras cada uno) | Haiku | Formato acotado, alto volumen |
| Meta descriptions (155 caracteres) para 50+ URLs | Haiku | Formato acotado |
| Generar snippet FAQ (Q + A ≤80 palabras) para schema | Haiku | Patrón repetitivo |
| Reescribir title tags para batch de páginas | Haiku | ≤60 caracteres, patrón |
| Clasificar keywords por intent (info/comm/trans/nav) — batch de 300 | Haiku | Clasificación binaria/categórica |
| Extraer entidades de un texto largo (para structured data) | Haiku | Extracción, no creación |
| Generar variantes de anchor text para link building interno | Haiku | Volumen, formato corto |

## Fallback

Si Haiku falla en calidad para una tarea que debería manejar → subir a Sonnet.
Si Sonnet duda o inventa → subir a Opus.
Nunca bajar de modelo si el previo funcionó bien; el costo marginal no justifica el riesgo.

## Registro obligatorio

Cada uso de LLM se registra en el `trajectory.md` de la pieza con:

```
- Prompt: prompts/pillar_page.md v1.2
- Modelo: claude-sonnet-4-6
- Input tokens: ~2400
- Output tokens: ~3800
- Costo estimado: $0.03
- Iteraciones: 2 (draft 1 rechazado por checkpoint 3, draft 2 aprobado)
```

Esto permite auditar costos y calibrar el routing en el tiempo.
