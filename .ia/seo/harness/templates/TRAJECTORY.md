---
slug: <slug-de-la-pieza>
piece_type: <pillar_page|cluster_post|product_faq|local_landing|video_description>
target_keyword: <keyword principal>
target_country: <CO|US|MX|AR|ES>
target_category: <web|images|video|shopping>
gap_source: content_gaps.csv#L<numero>
created: <YYYY-MM-DD>
published: <YYYY-MM-DD o null>
url: <URL final en producción o null>
---

# Trajectory — <título de la pieza>

## 1. Motivación (¿por qué esta pieza?)

Referencia al gap en `content_gaps.csv`: <fila>.

Descripción del gap:
- **Keyword objetivo:** <kw>
- **Volumen mensual:** <n>
- **Posición actual Maquiempanadas:** <n o "no aparece">
- **Posición rival dominante:** <n> (<dominio>)
- **Score gap:** <volumen × posición_rival>

## 2. Investigación del SERP

Snapshot del SERP objetivo estudiado:
- **Archivo:** `.ia/seo/data/dataforseo/YYYY-MM-DD_serp_<slug>.json`
- **Top 5 orgánicos:** <lista>
- **Features SERP:** <PAA, images, video, popular_products, AI_overview>
- **Ángulos usados por rivales:** <observación>

## 3. Benchmark cross-category (si aplica)

Referencias de nichos análogos revisados en `benchmark_categories.md`:
- <nicho>: <patrón replicado>

## 4. Draft y modelos usados

| Iteración | Modelo | Prompt | Notas |
|---|---|---|---|
| Draft 1 | <opus|sonnet|haiku> | `prompts/<tipo>.md` v<X> | <por qué se rechazó o aceptó> |
| Draft 2 | ... | ... | ... |

Costo total LLM estimado: $<n>

## 5. Checklist QC (13 checkpoints)

Cada uno con evidencia:

- [ ] 1. Autor identificado: <nombre + cargo>
- [ ] 2. Sin invenciones numéricas: fuentes → <lista>
- [ ] 3. Foto/video original: <archivo o URL>
- [ ] 4. Claims verificables: <lista con fuente>
- [ ] 5. Respuesta directa al inicio: <cita del párrafo>
- [ ] 6. Entidades reconocibles: <lista>
- [ ] 7. Datos concretos en primeras 500 palabras: <lista de datos>
- [ ] 8. Structured data válido: Rich Results Test → <screenshot o link>
- [ ] 9. Alt-text en todas las imágenes: <n imágenes / <n>>
- [ ] 10. Meta title (<= 60c) y description (<= 155c): <textos finales>
- [ ] 11. Enlace interno a pilar y producto: <lista>
- [ ] 12. Sin enlaces rotos: <verificado con qué herramienta>
- [ ] 13. Trajectory log completo: (este archivo)

## 6. Revisión humana

- **Revisor:** <nombre>
- **Fecha:** <YYYY-MM-DD>
- **Cambios pedidos:** <lista o "ninguno">
- **Aprobación final:** <sí/no>

## 7. Publicación

- **Fecha publicación:** <YYYY-MM-DD>
- **URL final:** <url>
- **Submit a Indexing API:** <sí/no> — <respuesta>
- **Screenshot post-publicación:** <link>

## 8. Seguimiento post-publicación

Se completa a t+30d, t+90d, t+180d:

| Snapshot | Fecha | Posición web | Posición imágenes | Impresiones | Clics | Notas |
|---|---|---|---|---|---|---|
| t+30d | | | | | | |
| t+90d | | | | | | |
| t+180d | | | | | | |
