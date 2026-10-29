---
slug: poa-residencia-ue-eori
title: Pregunta de residencia UE/Reino Unido y aviso EORI en el formulario POE
status: refined
created: 2026-10-29
author: Nicolás Navarro (My SEO Company)
---

# User Story (raw)

Como administración de MQE, quiero que el formulario POE pregunte si el cliente reside en la Unión Europea o Reino Unido y, en ese caso, le advierta sobre el número EORI, para evitar retrasos aduaneros en la importación de su pedido.

## Contexto crudo

Solicitud de la administración de MQE (WhatsApp, 28/10/2026):

> te escribo por que necesitamos hacer una ajuste en el campo del POA
> necesito un campo donde diga:
>
> 1. Residencia en Europa
>
> ¿Es usted residente en Europa?
> ☐ Sí
> ☐ No
>
> Si la respuesta es NO: se mantiene el formulario tal como está actualmente.
> Si la respuesta es SÍ: se debe desplegar el siguiente mensaje y la pregunta sobre el número EORI.
>
> 2. Información sobre el número EORI
>
> Importante: número EORI
>
> Le recordamos que, al residir en Europa, es posible que necesite contar con un número EORI (Economic Operators Registration and Identification) para realizar los trámites aduaneros correspondientes a la importación de su pedido.
>
> Este número puede solicitarse en línea ante la autoridad aduanera competente de su país. Le recomendamos verificar los requisitos aplicables según su lugar de residencia y las características de su importación.
>
> ¿Cuenta usted con un número EORI?
> ☐ Sí, cuento con número EORI.
> ☐ No, aún no cuento con número EORI.
>
> 3. Mensaje si no cuenta con número EORI
>
> Si la respuesta es NO, se debe desplegar el siguiente mensaje:
>
> Le recordamos que, al residir en Europa, es posible que necesite contar con un número EORI (Economic Operators Registration and Identification) para realizar los trámites aduaneros correspondientes a la importación de su pedido.
>
> Este número puede solicitarse en línea ante la autoridad aduanera competente de su país. Le recomendamos verificar los requisitos aplicables según su lugar de residencia y las características de su importación.

Respuestas a preguntas de aclaración (29/10/2026):

> 1. ok, entonces si, podemos ajustarlo a ¿Es usted residente de la unión europea o reino unido?
> 2. [La gerencia de MQE] quiere que se repita y que seamos incisivos en que necesita EORI
> 3. Solo es informativo
> 4. Si dejar en el POA CRM y que aparezca por favor

Nota: el cliente lo llama "POA"; en el código es el formulario POE (`/metadata/{customer}/create/poe/40`, campaña 40 "POE").

## Enlaces

- Conversación / ticket / issue de origen: WhatsApp con la administración de MQE, 28–29/10/2026
- Personas involucradas: administración MQE (solicitante), gerencia MQE (decisión sobre repetir el aviso), Nicolás Navarro (My SEO Company)
- Spec relacionado: `fix/poe-endpoint-hardening` (seguridad del mismo endpoint, pendiente)
