A continuación te propongo una arquitectura de alto nivel para identificar clientes calificados a partir de conversaciones almacenadas en wire_messages, aprovechando un LLM. Tomo como base que los mensajes tienen cuerpo (body), tipo (type), timestamps y están ligados a conversaciones (conversation_id) y participantes (sendable_id/sendable_type).
Arquitectura propuesta (LLM + calificación de clientes)
1) Ingesta y normalización de mensajes
Origen: tabla wire_messages, con body, type, created_at, conversation_id y relación con el remitente (sendable_id, sendable_type).
Pipeline:
Filtrar mensajes por type relevante (p.ej. text) y por rango de fechas.
Normalizar texto (limpieza, desduplicación, detección de idioma, etc.).
Persistir un “snapshot” de conversaciones ya ordenadas por created_at para procesamientos posteriores.
2) Ensamblado de conversaciones
Agrupar por conversation_id para construir el hilo conversacional.
Enriquecer con metadatos del remitente usando sendable_id/sendable_type (por ejemplo, cliente vs agente).
(Opcional) Relacionar con participantes si hay datos en wire_participants para roles y estado de la conversación.
3) Extracción de señales (features)
Ejecutar un pipeline de extracción por conversación:
Señales explícitas: intención de compra, presupuesto, urgencia, intereses, tamaño de empresa, etc.
Señales implícitas: sentimiento, tono, número de interacciones, tiempo de respuesta, etc.
Para esto puedes usar:
LLM para extraer un JSON estructurado (intención, necesidad, presupuesto, próxima acción).
Heurísticas complementarias (palabras clave, “pregunta por precio”, “agenda demo”, etc.).
4) Indexación semántica (vectorización)
Generar embeddings por conversación (o por ventanas de mensajes).
Guardar embeddings en un vector store (Pinecone, pgvector, Weaviate, etc.), junto con metadatos: conversation_id, fechas, remitente, etc.
Esto permite búsquedas tipo: “conversaciones con intención alta de compra” y recuperación semántica para el LLM.
5) Clasificación y scoring de leads
Definir un modelo de calificación:
Reglas (business rules) + score ponderado.
LLM classifier que devuelve un nivel (Ej.: Calificado / En nurturing / No calificado).
Ejemplo de inputs del LLM:
Resumen conversacional.
Señales extraídas (intención, presupuesto, urgencia).
Historial temporal (recencia, duración del hilo).
6) Orquestación y workflow
Un “orchestrator” (jobs/queues) que:
Procesa conversaciones nuevas o actualizadas.
Recalcula score cuando hay mensajes recientes.
Dispara acciones: alertas, tareas de seguimiento, asignación a vendedor.
7) Observabilidad y feedback loop
Guardar el resultado (score, clasificación y razones) en una tabla de analítica.
Permitir feedback humano (“este lead sí/no era bueno”) para recalibrar reglas o entrenar prompts y clasificadores.