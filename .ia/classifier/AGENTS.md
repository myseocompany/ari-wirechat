Objetivo
Clasificar conversaciones de `wire_messages` para detectar clientes realmente calificados, usando como entrada SOLO los mensajes del cliente (no lo que respondió el bot/chatbot).

Este plan aterriza la arquitectura a un MVP ejecutable con reglas claras, priorizando lo que hoy más importa para negocio:
- Ultra prioritario: el cliente pide cita para ir a la fábrica.
- Muy prioritario: el cliente pide llamada / reunión.
- Crítico: el cliente ya hace productos (no es solo “tengo un proyecto”).
- Crítico: el cliente produce > 500.

Fuente de verdad para señales
Usar como guía `.ia/prompt_sellerchat.md`, pero con una diferencia clave:
- Aquí NO tenemos el contexto completo del bot, solo el texto del cliente.
- Por lo tanto, debemos inferir señales directamente desde el `body` del cliente.

Plan MVP (ejecutable paso a paso)
La arquitectura anterior es correcta, pero demasiado amplia para empezar. Este es el recorte recomendado para ejecutar YA:

1) Recorte de datos (solo cliente)
Construir conversaciones por `conversation_id` filtrando:
- Solo mensajes tipo texto.
- Solo mensajes cuyo `sendable_type` corresponda al cliente (por ejemplo `App\\Models\\Customer`).
- Ordenados por `created_at` ascendente.

Salida mínima por conversación:
- `conversation_id`
- `customer_messages` (array de strings ordenado)
- `full_customer_text` (customer_messages unido con saltos de línea)
- `last_customer_message_at`
- `customer_message_count`

2) Extracción de señales (reglas primero, LLM después)
Antes de meter LLM, sacar señales con heurísticas simples y trazables.
El LLM puede venir después para refinar, pero no es necesario para el primer corte.

Señales prioritarias (business-first)
Las siguientes señales deben detectarse explícitamente:

2.1) Señales “hard intent” (las más importantes)
- `pide_cita_fabrica`: true/false
  Ejemplos: "quiero ir a la fábrica", "puedo visitar la planta", "agendemos cita en la fábrica".
- `pide_llamada`: true/false
  Ejemplos: "me llamas", "agendamos una llamada", "quiero una reunión", "podemos hablar".

2.2) Señales de operación real vs proyecto
- `tiene_productos`: true/false
  Debe activarse si el cliente habla de producir/hacer/vender actualmente.
  Ejemplos: "yo produzco", "vendo empanadas", "hago arepas".
- `solo_proyecto`: true/false
  Debe activarse si el cliente indica que aún no produce.
  Ejemplos: "tengo un proyecto", "quiero empezar", "aún no produzco", "estoy empezando".

Regla importante:
- Si hay señales claras de operación real y de proyecto a la vez, priorizar operación real.

2.3) Señales de volumen
- `volumen_mayor_500`: true/false
- `volumen_estimado`: int|null

Regla de negocio explícita:
- Si el cliente menciona un volumen >= 500, activamos `volumen_mayor_500 = true`.
- Si no hay número claro, dejar `volumen_estimado = null`.

2.4) Señales adicionales recomendadas (muy útiles)
Estas señales no deben desplazar a las prioritarias, pero ayudan mucho a subir o bajar confianza:

Dolor operativo y de tiempo
- `dolor_operarios`: true/false
  Ejemplos: "no consigo operarios", "los operarios fallan", "dependemos mucho del personal".
- `dolor_tiempo`: true/false
  Ejemplos: "me quita mucho tiempo", "es muy lento", "tardo demasiado", "no doy abasto".
- `dolor_merma_calidad`: true/false
  Ejemplos: "mucha merma", "se dañan", "mala calidad", "no salen uniformes".

Crecimiento y expansión
- `apertura_nuevo_punto`: true/false
  Ejemplos: "voy a abrir otro punto", "nuevo local", "nueva sede", "expandir".
- `demanda_supera_capacidad`: true/false
  Ejemplos: "tengo mucha demanda", "me piden más", "no alcanzo a cumplir".
- `habla_escalar`: true/false
  Ejemplos: "quiero crecer", "escalar", "automatizar", "aumentar producción".

Urgencia temporal
- `urgencia_alta`: true/false
  Ejemplos: "lo necesito ya", "este mes", "esta semana", "lo más pronto posible", "urgente".

Señales económicas y de compra real
- `tiene_presupuesto`: true/false
  Ejemplos: "tengo presupuesto", "tengo el dinero", "voy a invertir".
- `pide_cotizacion_o_ficha`: true/false
  Ejemplos: "envíame la cotización", "mándame la ficha técnica", "necesito la ficha".
- `pregunta_pago_logistica`: true/false
  Ejemplos: "cómo pago", "medios de pago", "financiación", "tiempos de entrega", "envío", "instalación", "repuestos", "mantenimiento".

Contexto profesional / negocio activo
- `negocio_activo_explicitado`: true/false
  Ejemplos: "tengo un negocio", "tengo restaurante/panadería", "vendo todos los días".

Reglas de interpretación sugeridas
- Si `tiene_productos = true`, entonces `solo_proyecto` no debe usarse para penalizar aunque aparezca lenguaje de "proyecto".
- Si aparece `pide_cita_fabrica = true`, no es necesario “esperar” otras señales para considerar `calificado`.

3) Scoring explícito y auditable (sin magia)
El scoring debe reflejar tus prioridades reales, no un promedio genérico.
Propuesta de score 0-100 con pesos fuertes:

Pesos recomendados (alineados a negocio)
- `pide_cita_fabrica`: +70
- `pide_llamada`: +40
- `tiene_productos`: +30
- `volumen_mayor_500`: +30
- `solo_proyecto`: -20

Pesos sugeridos para señales adicionales
Estos pesos son más pequeños y sirven como refuerzo:
- `dolor_operarios`: +12
- `dolor_tiempo`: +10
- `dolor_merma_calidad`: +8
- `apertura_nuevo_punto`: +15
- `demanda_supera_capacidad`: +15
- `habla_escalar`: +10
- `urgencia_alta`: +12
- `tiene_presupuesto`: +18
- `pide_cotizacion_o_ficha`: +15
- `pregunta_pago_logistica`: +10
- `negocio_activo_explicitado`: +12

Notas:
- El score se puede capear a 100.
- La “cita a fábrica” debe prácticamente garantizar estado alto.

4) Clasificación simple (3 estados)
Definir estados operativos claros y accionables:
- `calificado`
- `nurturing`
- `no_calificado`

Umbrales sugeridos:
- `calificado`: score >= 70 OR `pide_cita_fabrica = true`
- `nurturing`: score entre 40 y 69
- `no_calificado`: score < 40

5) Persistencia mínima (para poder operar)
Guardar el resultado por conversación en una tabla dedicada (nombre sugerido: `lead_classifications`), con:
- `conversation_id` (unique)
- `score` (int)
- `status` (string)
- `signals_json` (json)
- `reasons_json` (json)
- `last_customer_message_at` (datetime nullable)
- timestamps

6) Orquestación mínima (simple pero útil)
En lugar de una gran orquestación, arrancar con:
- Un comando que procese conversaciones recientes.
- Luego, si sirve, se lleva a jobs/queues.

Estrategia de ejecución:
- Procesar por ventana de tiempo (ej: últimos N días) o por `conversation_id`.
- Recalcular si hay mensajes nuevos del cliente.

Qué NO necesitamos en el MVP
Para evitar fricción y dependencia de infraestructura:
- No usar base vectorial todavía.
- No usar embeddings todavía.
- No cambiar dependencias.

El LLM se puede enchufar después solo para:
- Estandarizar extracción de señales a JSON.
- Generar un “resumen para humano” en `reasons_json`.

Definición operativa de “calificado” (lo más importante)
Una conversación debe tender fuertemente a “calificado” si:
- El cliente pide cita para ir a la fábrica, o
- El cliente pide llamada y además produce, o
- El cliente produce y menciona volumen >= 500.

Checklist de implementación (orden recomendado)
Este orden está pensado para ir ejecutando sin bloquearse:

Fase 1 (reglas + datos)
1) Construir snapshot por conversación SOLO con mensajes del cliente.
2) Implementar extractor heurístico de señales prioritarias.
3) Implementar scorer con los pesos definidos arriba.
4) Persistir resultado por `conversation_id`.

Fase 2 (operación)
5) Crear un comando para recalcular por rango de fechas / conversaciones recientes.
6) Exponer una vista tipo tabla para revisar: conversación, score, estado, señales y última actividad.

Fase 3 (refinamiento)
7) Enchufar LLM únicamente como extractor/normalizador y/o generador de razones.
8) Ajustar pesos y reglas con feedback humano.

Notas clave para no romper el enfoque
- Recordatorio crítico: aquí no existe la conversación completa. Solo tenemos texto del cliente.
- Por eso, las reglas deben ser robustas a mensajes sueltos y a contexto incompleto.
