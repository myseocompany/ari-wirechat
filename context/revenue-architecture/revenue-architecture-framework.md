# Revenue Architecture — marco operativo

## Regla central

Antes de proponer una iniciativa, clasificarla como **adquisición**, **retención** o **expansión**. No llamar indistintamente “palancas” a componentes de crecimiento, etapas del recorrido, segmentos, canales o métricas.

## Componentes de crecimiento

| Componente | Definición | Resultado que debe comprobarse |
| --- | --- | --- |
| Adquisición | Clientes nuevos e ingreso inicial atribuible | Ingreso nuevo cobrado por cohorte/origen |
| Retención | Valor de la base que se preserva | Abandono, contracción y valor protegido |
| Expansión | Valor adicional de clientes existentes | Ingreso incremental por cliente/cohorte |

En un periodo: `crecimiento neto = adquisición + expansión − contracción − abandono − notas crédito incrementales`.

La retención protege la base; no se suma como ingreso incremental sin definir cuál era el ingreso en riesgo.

## Recorrido Bowtie

El recorrido permite localizar fricción: awareness → educación → priorización → compromiso mutuo → onboarding → retención → expansión. Para cada etapa, definir estado observable, responsable, tiempo esperado y evidencia de avance.

En ARI WireChat, una conversación puede ser una señal de intención o seguimiento, pero no constituye venta ni ingreso. El sistema debe distinguir señal, oportunidad, gestión, avance y desenlace verificable.

## Métricas y restricción

Usar cuatro dimensiones:

- **Volumen:** casos que entran o están disponibles en una etapa.
- **Conversión:** proporción que avanza a la siguiente etapa.
- **Tiempo:** demora entre etapas o hasta la gestión.
- **Valor:** ingreso neto, margen o valor protegido, con fuente confiable.

Mantener hasta tres métricas como foco ejecutivo, sin impedir mejoras coordinadas en otros puntos del sistema. Cada cambio debe conservar hipótesis, dueño y medición para no perder causalidad.

## Datos mínimos

- Cohorte, origen y segmento, mantenidos como dimensiones separadas.
- Estado de recorrido, dueño responsable y siguiente paso.
- Tiempos entre señal, contacto, gestión, avance y desenlace.
- Resultado comercial verificable y su fuente, cuando exista.
- Evidencia de la señal y de las alertas o acciones realizadas.

## Criterios de decisión

- No escalar adquisición si atención, agenda o seguimiento son la restricción.
- No atribuir una respuesta, lectura, envío o conversación a ingreso.
- Evaluar iniciativas por componente de crecimiento, costo y resultado; no solo por volumen.
- Para toda propuesta, documentar componente, hipótesis, cohorte, métrica base, meta, capacidad, propietario y fecha de revisión.
- Proteger conversaciones y datos personales: minimizar datos, conservar trazabilidad y usar datos ficticios o anonimizados fuera de producción.
