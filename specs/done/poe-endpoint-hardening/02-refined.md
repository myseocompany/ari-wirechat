---
slug: poe-endpoint-hardening
refined_at: 2026-10-29
---

# Refined User Story

Clasificación de la iniciativa: **retención** (protege el proceso post-venta de despacho aduanero y los datos de clientes que ya compraron; no busca nuevos clientes).

Como responsable del CRM de MQE, quiero que el formulario POE público acepte solo los campos que la campaña pide, para un cliente legítimo y con enlace válido, y que rechace archivos, campos ajenos y abusos, para que un tercero no pueda ejecutar código en el servidor, alterar fichas de clientes ni enumerar nombres, sin que los clientes reales dejen de poder enviar sus datos de despacho.

## Actor

- **Ejecuta**: (a) el cliente final de MQE que diligencia el POE desde el enlace por WhatsApp; (b) el asesor de MQE que copia el enlace desde la ficha del cliente; (c) un atacante anónimo que conoce o adivina la URL.
- **Se benefician**: MQE (integridad de los datos aduaneros y del CRM), sus clientes (confidencialidad de su nombre y datos), My SEO Company (servidor sin ejecución remota de código).

## Valor

- **Ejecución remota de código evitada**: nginx en producción pasa cualquier `*.php` bajo `public/` a PHP-FPM (`location ~ \.php$`, verificado el 29/10/2026). Hoy un atacante anónimo puede subir un `.php` por este endpoint y ejecutarlo. No hay archivos subidos aún (las carpetas destino no existen; verificado 29/10/2026), así que el riesgo no se ha materializado.
- **Integridad del CRM**: cualquiera puede escribir respuestas POE en cualquiera de los clientes (IDs secuenciales). Hay 256 respuestas huérfanas históricas (2023: 121, 2024: 70, 2025: 65; 0 en 2026; fuente: `customer_metas` de producción con `audience_id = 67`, consulta del 29/10/2026).
- **Confidencialidad**: recorriendo `/metadata/{id}/create/poe/40` se obtiene el nombre de cada cliente.
- **Dato correcto en el POA**: el tipo de importación aparece con la etiqueta equivocada ("Veracidad de la información") en todos los envíos.
- Volumen a proteger: 106 envíos en 90 días (2026-07-31 → 2026-10-28; pares distintos cliente + fecha).

## Criterios de aceptación

- **AC1 — Archivos rechazados**: Given cualquier POST al endpoint POE, When el request incluye uno o más archivos (cualquier nombre de campo, incluido `meta_55`), Then no se escribe ningún archivo en disco ni se crea ningún registro de documento, y el envío del resto de campos se procesa con las mismas reglas que sin archivos.
- **AC2 — `project_id` ignorado**: Given un POST con `project_id` (incluido `../..`), When se procesa, Then ese valor no se usa para ninguna ruta ni registro.
- **AC3 — Solo campos de la campaña**: Given un POST con campos `meta_<id>`, `radio_<id>` o `check_<id>`, When alguno no pertenece a las preguntas de la campaña del formulario (hijos de sus grupos, más el tipo de importación), Then ese campo no se guarda; los demás sí.
- **AC4 — `audience_id` del servidor**: Given un POST con `audience_id` arbitrario, When se guardan las respuestas, Then `customer_metas.audience_id` toma el valor fijado por el servidor para el POE (67), no el del request.
- **AC5 — Cliente inexistente**: Given un `customer_id` que no existe, When se abre el formulario o se envía, Then la respuesta es 404 y no se guarda ninguna fila (ni error 500).
- **AC6 — Enlace firmado**: Given un enlace generado por el botón "POA" de la ficha del cliente, When el cliente lo abre y envía, Then funciona igual que hoy; Given una URL con un `customer_id` modificado o sin firma válida, When se abre o se envía, Then se rechaza sin mostrar el nombre del cliente (ver supuesto de periodo de transición).
- **AC7 — Validación en el servidor**: Given un envío al que le falta un campo obligatorio (contacto, destino, declaración de veracidad, residencia UE; datos de compañía cuando el tipo de importación no es "Individual"; EORI cuando residencia = Sí), o con un valor de radio que no es una de sus opciones, When se envía, Then no se guarda nada y el cliente vuelve al formulario con un mensaje de error y sus datos conservados.
- **AC8 — Límite de envíos**: Given una misma IP, When supera el límite de envíos por minuto al endpoint POE, Then recibe 429 y no se guarda nada.
- **AC9 — Bloque de credenciales eliminado**: Given un POST con campos `login_*`, When se procesa, Then no se crea ningún registro de credenciales ni se produce error.
- **AC10 — Todo o nada**: Given un envío válido, When ocurre un error a mitad del guardado, Then no queda guardada ninguna de sus respuestas.
- **AC11 — Tipo de importación con su etiqueta**: Given un envío nuevo, When se abre el acordeón POA del cliente, Then el tipo de importación aparece como "Importación de manera" (o la etiqueta que se confirme) y no como "Veracidad de la información".
- **AC12 — Camino feliz intacto**: Given un cliente con enlace válido que llena el formulario completo (incluida la residencia UE/EORI), When envía, Then se guardan exactamente las mismas respuestas que hoy (salvo AC11) y se le redirige a maquiempanadas.com.
- **AC13 — Rechazos registrados**: Given cualquier rechazo de AC1, AC3, AC5, AC6, AC7 o AC8, When ocurre, Then queda un registro en el log con motivo, IP, `customer_id` y campaña, sin los valores enviados por el cliente.

## Requisitos no funcionales

- **Performance**: la validación y el guardado no deben añadir más de ~100 ms al envío; el formulario sigue siendo una sola página sin dependencias nuevas de front.
- **Seguridad**: endpoint público sin login. Ningún dato del request decide rutas de archivo, cliente destino (fuera del enlace firmado), audiencia ni campos fuera de la campaña. Mantener CSRF. No registrar en logs PII ni valores enviados.
- **Multi-tenancy**: la instancia arichat.co es de MQE; las reglas aplican al POE de la campaña 40 y a cualquier campaña que se abra por esta ruta (los campos válidos se derivan de la campaña).
- **Observabilidad**: log de rechazos (AC13) para detectar abuso y para medir falsos positivos en la primera semana.
- **i18n / accesibilidad**: mensajes de error en español, visibles al inicio del formulario y legibles en móvil.
- **Compatibilidad**: Chrome y Safari móviles actuales; enlaces ya enviados por WhatsApp según el supuesto de transición.

## Supuestos

- [CONFIRMAR] **Enlace firmado con periodo de transición**: los enlaces nuevos del botón "POA" llevan firma; los enlaces sin firma ya enviados siguen funcionando durante **30 días** (con log) y luego se rechazan. Alternativa: rechazarlos desde el primer día.
- [CONFIRMAR] El enlace firmado **no expira** (un cliente puede abrirlo semanas después).
- [CONFIRMAR] Con enlace válido, la página puede seguir mostrando el **nombre completo** del cliente.
- [CONFIRMAR] Límite de envíos: **5 por minuto por IP** (los clientes envían 1 vez; 226 clientes han enviado más de una vez en la historia, nunca en ráfaga conocida).
- [CONFIRMAR] No se añade captcha; enlace firmado + límite se consideran suficientes.
- [CONFIRMAR] Los campos de "Información de la compañía" son obligatorios solo cuando el tipo de importación no es "Individual" (como hace hoy el JS).
- [CONFIRMAR] Para el tipo de importación se crea su propio campo con la etiqueta **"Importación de manera"**, y las respuestas históricas guardadas bajo el grupo 34 se **reasignan** a ese campo para que el POA de clientes anteriores también se corrija.
- [CONFIRMAR] Si la validación falla, el cliente vuelve al formulario con sus datos conservados (no a una página de error).
- [CONFIRMAR] Las 256 respuestas huérfanas históricas **no** se borran en este feature.
- [CONFIRMAR] El formulario POE solo admite subida de archivos en el futuro si se especifica en otro spec (hoy ninguna campaña POE lo usa).
- [VERIFICADO] El único generador del enlace es el botón "POA" de `customers/show` (no hay plantillas de mensajes con el enlace; consulta del 29/10/2026).
- [VERIFICADO] El bloque `login_*` es código muerto: la tabla `project_logins` no existe en producción y ninguna vista envía esos campos.
- [VERIFICADO] Ninguna respuesta con `audience_id = 67` usa campos ajenos a la campaña 40.

## Riesgos

- **Blast radius**: el POE alimenta el despacho aduanero (~35 envíos/mes). Una validación demasiado estricta, un límite mal calibrado o un enlace firmado que no valide bloquearía a clientes reales que ya pagaron. Debe probarse el camino feliz completo (AC12) en producción con el cliente de prueba antes de anunciar.
- **Enlaces ya enviados**: si se exige firma sin transición, todo cliente con un enlace viejo en su WhatsApp queda bloqueado. La transición mitiga, pero deja la enumeración abierta durante ese periodo.
- **Reversibilidad**: alta para el código (revert). La reasignación de respuestas históricas del grupo 34 requiere migración de datos con rollback explícito.
- **Dependencias externas**: aviso a los asesores de MQE para que copien el enlace nuevo desde el botón "POA" a partir del deploy; aprobación humana para el deploy y la migración de datos en producción.
- **Impacto en tenants activos**: solo MQE (campaña 40). `create3.blade.php` también envía a este endpoint pero ninguna ruta lo muestra.
- **Proxy/IP**: si el servidor está detrás de un proxy, el límite por IP podría agrupar clientes; verificar la IP real en la fase de propuesta.

## Fuera de alcance

- Rediseño visual o funcional del formulario POE.
- Captcha (salvo que se decida en supuestos).
- Borrar o depurar las 256 respuestas huérfanas históricas.
- Endurecer otros formularios de `metadata/*` (encuestas, `freeClass`, `schedule`) — candidato a otro spec.
- Habilitar subida segura de documentos en el POE.
- Cambiar la redirección final a maquiempanadas.com.
- Revisar la configuración general de nginx (p. ej. restringir PHP a `index.php`); se recomienda como tarea de infraestructura aparte.
