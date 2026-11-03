---
slug: poe-endpoint-hardening
title: Endurecer el endpoint público del formulario POE
status: archived
created: 2026-10-29
author: Nicolás Navarro (My SEO Company)
---

# User Story (raw)

Como My SEO Company, responsable del CRM de MQE, quiero que el formulario POE público solo acepte los datos que el formulario realmente pide, para el cliente correcto, para que nadie pueda subir archivos ejecutables, escribir en la ficha de otros clientes ni ver nombres de clientes recorriendo URLs.

## Contexto crudo

Pedido de Nicolás Navarro (29/10/2026): "haz le fix/poe-endpoint-hardening".

Hallazgos de la revisión de seguridad del 28-29/10/2026 sobre el formulario POE (GET `metadata/{id}/create/poe/{cid}` → `MetaDataController::createPoe`; POST `metadata/{id}/store/poe` → `storepoe` + `saveCustomerMeta`; rutas sin auth):

1. CRÍTICO — subida de archivos sin validación: `storepoe` recorre todos los archivos del request y los guarda con su nombre original en una carpeta pública; `saveCustomerMeta` hace lo mismo para `meta_55`. La campaña 40 no tiene campo de archivo.
2. CRÍTICO — la carpeta de destino usa `project_id` del request sin sanear (path traversal).
3. ALTO — escritura en cualquier cliente y cualquier campo: `customer_id` sale de la URL y el ID del campo sale del nombre del input sin validar que pertenezca a la campaña; `audience_id` sale del request. 256 respuestas huérfanas (cliente inexistente) en producción.
4. ALTO — enumeración: IDs secuenciales y la página muestra el nombre del cliente.
5. El bloque `login_*` crea registros con contraseñas en texto plano desde un formulario público.
6. Sin límite de envíos, sin captcha, sin validación en el servidor (el `required` solo existe en el HTML; la residencia UE obligatoria solo se exige en el navegador).
7. Error 500 si el cliente no existe; sin transacción.

Bug de diseño visible: el select "Importación de manera" se envía como `meta_34`, que es el ID del grupo "Veracidad de la información"; en el acordeón POA aparece "Veracidad de la información = Individual" (confirmado en prueba de producción del 29/10/2026).

Contexto: 106 envíos en 90 días (audience 67); solo la campaña 40 (MQE) usa el formulario; los asesores copian el enlace con el botón "POA" de la ficha del cliente y lo envían por WhatsApp a clientes que ya compraron.

## Enlaces

- Conversación / ticket / issue de origen: sesión de trabajo 28-29/10/2026 (revisión de seguridad del endpoint POE)
- Personas involucradas: Nicolás Navarro (solicitante); administración MQE (usuaria del formulario); asesores MQE (envían el enlace)
- Spec relacionado: `specs/done/poa-residencia-ue-eori/`
