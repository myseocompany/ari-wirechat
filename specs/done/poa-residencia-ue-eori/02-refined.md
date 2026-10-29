---
slug: poa-residencia-ue-eori
refined_at: 2026-10-29
---

# Refined User Story

Clasificación de la iniciativa: **retención** (experiencia post-venta: el cliente ya compró y el POE alimenta el trámite aduanero de su pedido).

Como cliente de MQE que reside en la Unión Europea o el Reino Unido, quiero que el formulario POE me advierta que probablemente necesito un número EORI, para tramitarlo a tiempo y que la importación de mi máquina no se retrase en aduana. Como asesor/administración de MQE, quiero ver esas respuestas en el acordeón POA del CRM, para anticipar problemas aduaneros.

## Actor

- **Ejecuta**: el cliente final de MQE que diligencia el formulario POE público (sin login) desde el enlace que le envía el asesor.
- **Se beneficia**: (a) el cliente, que se entera del requisito EORI antes del despacho; (b) la administración y los asesores de MQE, que ven en el CRM si el cliente reside en UE/Reino Unido y si declara tener EORI.
- No son la misma persona.

## Valor

- Evitar que un pedido llegue a la aduana de la UE/Reino Unido sin EORI del importador, lo que suele implicar retención de la mercancía, costos de bodegaje y reprocesos con el courier.
- Volumen de referencia: 106 envíos del formulario POE en los últimos 90 días (fuente: `customer_metas` de producción, `audience_id = 67`; periodo 2026-07-31 → 2026-10-28; método: pares distintos `customer_id` + `created_at`). No se conoce cuántos corresponden a UE/Reino Unido; esta feature empieza a medirlo.
- El cambio es informativo: no se cuantifica ahorro hasta tener la línea base que esta misma feature genera.

## Criterios de aceptación

- **AC1** — Given un cliente abre el formulario POE de la campaña 40 y elige el tipo de importación, When se muestran las secciones del formulario, Then aparece la pregunta "¿Es usted residente de la Unión Europea o Reino Unido?" con exactamente dos opciones excluyentes: "Sí" y "No".
- **AC2** — Given el cliente responde "No" a la residencia, When revisa el formulario, Then no se muestra el aviso EORI ni la pregunta EORI, y el resto del formulario y su envío funcionan exactamente igual que hoy.
- **AC3** — Given el cliente responde "Sí" a la residencia, When se actualiza el formulario, Then se muestra el aviso "Importante: número EORI" con el texto literal aprobado por el cliente, seguido de la pregunta "¿Cuenta usted con un número EORI?" con las opciones "Sí, cuento con número EORI." y "No, aún no cuento con número EORI.".
- **AC4** — Given residencia = "Sí" y EORI = "No, aún no cuento con número EORI.", When se actualiza el formulario, Then se muestra **una segunda vez** el mensaje de recordatorio EORI (texto literal, repetición intencional por decisión de la gerencia de MQE), destacado visualmente como advertencia.
- **AC5** — Given residencia = "Sí" y EORI = "Sí, cuento con número EORI.", When se actualiza el formulario, Then no se muestra el segundo mensaje.
- **AC6** — Given el cliente cambia la residencia de "Sí" a "No", When se actualiza el formulario, Then se ocultan el aviso, la pregunta EORI y el segundo mensaje, y la respuesta EORI previa no se envía.
- **AC7** — Given cualquier combinación de respuestas EORI (incluida "No, aún no cuento con número EORI."), When el cliente envía el formulario con el resto de campos válidos, Then el envío se acepta (la pregunta EORI nunca bloquea el envío) y no se genera notificación a ningún asesor.
- **AC8** — Given un envío con residencia = "Sí" y una respuesta EORI, When se guarda, Then quedan registradas para ese cliente ambas respuestas (residencia y EORI) como parte del mismo envío.
- **AC9** — Given un envío con residencia = "No", When se guarda, Then queda registrada la respuesta de residencia y no se registra respuesta EORI.
- **AC10** — Given un cliente con un envío POE que incluye estas respuestas, When un usuario del CRM abre el acordeón POA del cliente, Then ve las preguntas de residencia y EORI con sus respuestas, dentro del bloque de fecha de ese envío.
- **AC11** — Given un cliente con envíos POE anteriores a esta feature, When se abre su acordeón POA, Then se muestra igual que hoy (sin filas vacías ni errores).

## Requisitos no funcionales

- **Performance**: la lógica de mostrar/ocultar ocurre en el navegador, sin peticiones adicionales; el tiempo de carga del formulario no cambia de forma perceptible.
- **Seguridad**: el endpoint sigue siendo público. Esta feature no debe ampliar su superficie (ni nuevos tipos de archivo, ni nuevos parámetros libres). Las vulnerabilidades ya identificadas se atienden en `fix/poe-endpoint-hardening`. No se captura el número EORI en sí (solo Sí/No), por lo que no se añade PII nueva.
- **Multi-tenancy**: la instancia `arichat.co` es de MQE (logo y redirección fijos). El cambio aplica solo a la campaña 40 "POE"; otras campañas que usen el mismo formulario no deben mostrar las preguntas nuevas.
- **Observabilidad**: las respuestas quedan en `customer_metas` y permiten contar en el CRM cuántos clientes UE/Reino Unido sin EORI hay por periodo. No se añaden logs nuevos.
- **i18n / accesibilidad**: textos solo en español, literales del cliente (salvo "Europa" → "la Unión Europea o el Reino Unido" donde el cliente lo autorizó). Opciones navegables con teclado y con etiqueta asociada.
- **Compatibilidad**: el formulario se abre mayormente desde el celular (enlace por WhatsApp); debe funcionar en Chrome/Safari móviles actuales.

## Supuestos

Confirmados por la administración de MQE (WhatsApp, 29/10/2026 10:34):

- [CONFIRMADO] La pregunta de residencia es **obligatoria** (el cliente debe responder Sí o No para enviar).
- [CONFIRMADO] La pregunta "¿Cuenta usted con un número EORI?" es **obligatoria cuando se muestra** (residencia = Sí), aunque cualquier respuesta permite enviar.
- [CONFIRMADO] Ubicación: el bloque nuevo va después de "Información de destino" (país de entrega) y antes de "Veracidad de la información".
- [CONFIRMADO] En los textos del aviso, "al residir en Europa" se cambia a "al residir en la Unión Europea o el Reino Unido", para ser coherente con la pregunta.
- [CONFIRMADO] El bloque se muestra para todos los tipos de importación, "incluso y especialmente" Individual.
- [CONFIRMADO] No se captura el número EORI (solo si lo tiene).

Verificados o inferidos por My SEO Company:

- [VERIFICADO] La campaña 40 es la única que usa el formulario POE en producción (fuente: `campaign_customer_meta_data` en producción, 29/10/2026; los grupos 14/20/28/34 solo están vinculados a la campaña 40, y en los últimos 90 días las respuestas POE solo llegaron con `audience_id = 67`).
- [INFERIDO] En el CRM basta con ver las respuestas en el acordeón POA, sin filtros, columnas ni reportes (respuesta 4 del 29/10/2026: "dejar en el POA CRM y que aparezca").

## Riesgos

- **Blast radius**: el POE es la fuente de datos para el trámite aduanero con el courier. Si la lógica condicional deja un campo oculto marcado como obligatorio, el formulario no se puede enviar y se frenan despachos (~35 envíos/mes). Debe probarse cada rama (No / Sí+Sí / Sí+No / cambio Sí→No) antes de publicar.
- **Reversibilidad**: alta. Las preguntas se pueden desvincular de la campaña y la vista se puede revertir; las respuestas guardadas son filas nuevas que no alteran las existentes.
- **Dependencias externas**: aprobación de MQE de los textos finales y de los supuestos; cambio de configuración en la base de producción (requiere aprobación humana según políticas).
- **Impacto en tenants activos**: solo MQE (campaña 40). El endpoint tiene vulnerabilidades conocidas (subida de archivos, escritura en cualquier cliente); esta feature toca la misma vista y controlador, así que conviene coordinar el orden con `fix/poe-endpoint-hardening` para evitar conflictos.
- **Datos**: hay 256 respuestas huérfanas (cliente inexistente) y 226 clientes con más de un envío; el acordeón POA ya muestra múltiples envíos por fecha, así que un cliente puede ver respuestas EORI distintas en envíos distintos. Es esperado.

## Fuera de alcance

- Corregir las vulnerabilidades del endpoint POE (spec `fix/poe-endpoint-hardening`).
- Capturar o validar el número EORI.
- Deducir la residencia a partir del campo "País".
- Notificar al asesor o crear tareas/alertas cuando el cliente no tiene EORI.
- Construir un motor genérico de preguntas condicionales para todos los formularios de metadata.
- Traducir el formulario a otros idiomas.
- Reportes o filtros del CRM por residencia/EORI.
