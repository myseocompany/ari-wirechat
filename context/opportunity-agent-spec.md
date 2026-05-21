# SPEC: Agente de oportunidades comerciales

## Objetivo

Convertir conversaciones y campos del CRM en una lista accionable para ventas. El agente no debe limitarse a priorizar prospectos; debe explicar por qué importan y recomendar la siguiente acción.

## Principios

- Usar primero datos estructurados del CRM: `maker`, `count_empanadas`, estado, asesor, origen, etiquetas y acciones.
- Usar conversación como respaldo para inferir intención, producción, evidencia y contexto.
- Mantener un scoring híbrido, explicable y auditable.
- Activar LLM solo como apoyo para casos ambiguos o incompletos.
- Procesar LLM fuera del request web mediante cron/comando y guardar resultados por cliente.
- No auto-enviar mensajes desde este agente. Solo sugerir acción y texto.

## Entradas

- Campos de `customers`: nombre, teléfono, email, `maker`, `count_empanadas`, estado, asesor, origen.
- Mensajes recientes de `wire_messages`.
- Acciones humanas recientes de `actions`.
- Etiquetas del cliente.
- Filtros del reporte: fechas, prioridad, producción, asesor, origen, etiquetas, texto y límite.

## Salidas por prospecto

- Prioridad: alta, media o baja.
- Score numérico.
- Produce empanadas: sí, proyecto, otro o no claro.
- Producción diaria estimada.
- Intención: comprar, cotizar, información, evento, soporte o no claro.
- Evidencia textual.
- Motivos del score.
- Últimos mensajes.
- Últimas acciones humanas.
- Asesor y estado.
- Análisis IA, confianza y evidencia IA cuando aplique.
- Siguiente mejor acción.
- Canal recomendado.
- SLA recomendado.
- Razón de la acción.
- Mensaje sugerido corto cuando aplique.
- Condición de salida.

## Orden de prioridad

1. Produce empanadas.
2. Mayor producción diaria estimada.
3. Último mensaje del cliente sin acción posterior.
4. Intención de compra o cotización.
5. Score actual.
6. Recencia y actividad de conversación.

## Acciones recomendadas

- `reply_whatsapp`: responder por WhatsApp.
- `create_call_task`: crear tarea de llamada.
- `send_quote`: enviar o preparar cotización.
- `book_demo`: proponer demo o reunión.
- `qualify_project`: calificar proyecto antes de venta.
- `assign_owner`: asignar asesor.
- `wait_for_signal`: esperar nueva señal.
- `disqualify`: descartar o revisar después.

## Reglas iniciales de next-best-action

- Si el cliente respondió después de la última acción humana, recomendar respuesta por WhatsApp hoy.
- Si produce empanadas y tiene volumen claro alto, recomendar llamada o cotización según intención.
- Si pide precio, cotización o ficha, recomendar cotización o respuesta comercial breve.
- Si está sin asesor, recomendar asignación antes de seguimiento.
- Si es proyecto, recomendar calificación consultiva.
- Si el estado es ganado, posventa, repetido o baja, recomendar esperar señal o revisar después.
- Si la IA se activa, puede refinar acción, canal, SLA, razón, mensaje y condición de salida.

## LLM

El LLM debe devolver JSON estructurado. No reemplaza `maker` ni `count_empanadas` cuando esos campos son claros; solo completa señales faltantes o ambiguas.

La UI no debe llamar OpenAI directamente. El reporte genera/lee la lista de oportunidades con reglas y aplica resultados IA cacheados desde `opportunity_llm_analyses`. El procesamiento IA se ejecuta con `opportunities:detect --llm` desde CLI o scheduler.

Campos esperados:

```json
{
  "produce_empanadas": "yes|no|unknown|other",
  "estimated_daily_empanadas": 500,
  "intent": "buy|quote|info|event|support|unknown",
  "confidence": 0.82,
  "evidence": "frase corta tomada del texto",
  "next_best_action": "reply_whatsapp|create_call_task|send_quote|book_demo|qualify_project|assign_owner|wait_for_signal|disqualify",
  "recommended_channel": "whatsapp|phone|email|crm|none",
  "recommended_sla": "hoy|24h|48h|esta_semana|esperar",
  "action_reason": "razón breve",
  "suggested_message": "mensaje corto o null",
  "stop_condition": "condición de salida breve"
}
```

## Fuera de alcance por ahora

- Auto-envío de WhatsApp.
- Bandits, reinforcement learning o scoring predictivo entrenado.
- Cambios de dependencias.
- Templates aprobados de WhatsApp.
