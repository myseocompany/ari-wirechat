---
skill: content_qc
version: 1.0
applies_to: [pillar_page, cluster_post, product_faq, local_landing, video_description]
enforced_at: [pre_publish, pre_submit_indexing]
---

# SKILL — Content Quality Control (13 checkpoints)

Checklist obligatorio para toda pieza antes de publicar. Cada punto debe marcarse ✓ en el `trajectory.md` de la pieza. Si algún punto falla, la pieza no se publica.

## Contenido — veracidad y E-E-A-T

- [ ] **1. Autor identificado.** La página tiene un autor real (nombre + cargo). Ejemplo: `Autor: Camilo Ríos — Ingeniero de aplicaciones, Maquiempanadas`. No "el equipo", no "admin".

- [ ] **2. Sin invenciones numéricas.** Toda cifra sobre productos (precio, capacidad, kg/hora, dimensiones, potencia) está validada contra ficha oficial del producto o el spec sheet. Referencia interna citada en `trajectory.md`.

- [ ] **3. Foto o video original.** Al menos un activo audiovisual propio de Maquiempanadas embebido en la página. No stock genérico ni foto de rival.

- [ ] **4. Claims verificables.** Cualquier afirmación tipo "reduce tiempos 60%", "ahorra 3 empleados" viene con fuente o testimonio citable (nombre del cliente + ciudad, o link a estudio interno).

## Estructura — para AI Overviews y humanos

- [ ] **5. Respuesta directa al inicio.** El primer párrafo responde la query de forma directa en ≤50 palabras. AI Overviews prefieren respuestas explícitas y factuales.

- [ ] **6. Entidades reconocibles.** La página menciona por nombre las entidades relevantes: modelos de producto (CM05S, CM06B), marca, ciudad si aplica, categoría de comida (empanada colombiana, arepa venezolana), tipo de proceso.

- [ ] **7. Datos concretos en primeras 500 palabras.** Al menos 3 datos concretos con unidad (por ejemplo: 3,000 empanadas/hora, 220V, 45 kg, 6 meses de garantía).

## Estructura — SEO técnico

- [ ] **8. Structured data válido.** JSON-LD con el tipo correcto (Product, VideoObject, FAQPage, HowTo, LocalBusiness) validado con Google Rich Results Test. Screenshot o link en `trajectory.md`.

- [ ] **9. Alt-text en todas las imágenes.** Cada `<img>` tiene `alt` descriptivo (no "imagen" ni "foto"). Alt en el idioma de la página. Bilingüe si la imagen se comparte entre versiones `/en/` y `/es/`.

- [ ] **10. Meta title y meta description optimizados.** Title ≤60 caracteres, incluye keyword objetivo. Description ≤155 caracteres, con CTA implícito. Ninguno duplicado con otras URLs del sitio.

## Estructura — Interlinking

- [ ] **11. Enlace interno a al menos 1 pilar y 1 producto.** Toda pieza cluster enlaza al pilar de su tema principal + a al menos 1 URL de producto de Maquiempanadas.

- [ ] **12. Sin enlaces externos rotos.** Todo `<a href>` externo funciona (verificado). No linkear a rivales directos salvo caso justificado (comparativa).

## Trazabilidad

- [ ] **13. Trajectory log completo.** El archivo `content/<slug>/trajectory.md` está lleno con: gap atacado, keyword objetivo, SERP estudiado (screenshot o snapshot), prompt LLM usado, modelo (Opus/Sonnet/Haiku), draft rechazados y por qué, revisor humano, timestamp de publicación, submit al Indexing API (si aplica).

---

## Cómo el agente aplica este SKILL

Antes de escribir cualquier pieza, el agente lee este archivo y lo tiene presente. Al generar el draft, marca cada punto como `[ ]`, `[?]` (pendiente) o `[✓]` (cumplido). Nunca marca `[✓]` sin haber verificado.

Al terminar el draft, el agente llama a este checklist como último paso. Si algún punto está `[?]` o `[ ]`, el draft no se envía a revisión humana.

El humano revisor tiene autoridad para marcar puntos como incumplidos que el agente creyó cumplidos. En ese caso, se corrige y se re-verifica el checklist entero.

## Extensiones por tipo de contenido

- **Pillar page:** además de los 13, requiere: tabla de comparación entre modelos + FAQ ≥6 preguntas + video embed + HowTo schema.
- **Product FAQ:** los 4 PAA del SERP objetivo deben estar respondidos + FAQPage schema.
- **Local landing:** LocalBusiness schema con dirección real + mapa embebido + Google Business Profile enlazado.
- **Video description:** timestamps chapter markers + transcripción parcial + link a producto.
