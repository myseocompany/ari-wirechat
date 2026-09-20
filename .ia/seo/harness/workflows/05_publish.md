# Workflow — PUBLISH

## Objetivo

Llevar la pieza aprobada a producción con submit al Google Indexing API y trajectory cerrado.

## Pre-requisitos (bloqueantes)

- [ ] Draft con aprobación humana explícita (trajectory paso 6).
- [ ] `[✓]` en los 13 checkpoints de `SKILL_content_qc.md`.
- [ ] Rich Results Test aprobado.
- [ ] Assets originales listos (fotos, videos).

## Pasos

1. **Solicitar aprobación de publicación.**
   - `cp harness/templates/APPROVAL.md content/<slug>/APPROVAL.md`.
   - Nicolás confirma en `decision: approved`.

2. **Publicar.**
   - Cliente sube la URL a producción (o MySEO lo hace vía admin del sitio).
   - Verificar URL en vivo con `curl -I <url>` → 200 OK.
   - Verificar structured data en vivo con Rich Results Test.

3. **Submit al Google Indexing API (Julian Goldie insight).**
   - Endpoint: `https://indexing.googleapis.com/v3/urlNotifications:publish`.
   - Payload:
     ```json
     {
       "url": "https://maquiempanadas.com/<nueva-url>",
       "type": "URL_UPDATED"
     }
     ```
   - Nota: requiere Service Account con permiso Owner en Search Console.
   - Alternativa (si el Indexing API no está configurado): manual `Request Indexing` desde GSC UI.
   - Registrar respuesta en trajectory paso 7.

4. **Actualizar sitemap.xml.**
   - Confirmar que la URL nueva aparece en `sitemap.xml`.
   - Ping a Google: `https://www.google.com/ping?sitemap=https://maquiempanadas.com/sitemap.xml`.

5. **Cerrar trajectory.**
   - Completar paso 7: fecha, URL final, submit response, screenshot post-publicación.
   - Estado en `content_ledger.md` pasa a `published`.

6. **Iniciar monitoreo.**
   - Agregar la URL al set de tracking semanal (`workflows/06_monitor.md`).
   - Fijar baseline `t0`: primera medición dentro de 48h de publicar.

## Enlaces externos que empujan indexación

Después de publicar, considerar (solo si aplica):
- Compartir en LinkedIn / X con link (efecto discovery indirecto).
- Enviar a newsletter de la cliente (si existe).
- Notificar en foros de industria relevantes (siempre orgánico, nunca spam).

## Techo de gasto en este paso

**$0** (no hay DataForSEO en publicación). Solo el submit al Indexing API que es gratis.

## No hacer

- No publicar sin approval humano.
- No hacer submit al Indexing API si Nicolás no ha confirmado.
- No cambiar la URL después de publicar sin plan de redirect 301.
- No borrar el trajectory.md nunca.
