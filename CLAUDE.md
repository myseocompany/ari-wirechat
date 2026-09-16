# ARI WireChat — Contexto para Claude Code

## Qué es este proyecto

**ARI WireChat** es un SaaS de inbox multiagente (WhatsApp, Instagram, otros canales) sobre el que se construye el **Supervisor de Ventas IA**: un sistema que analiza conversaciones, detecta oportunidades desatendidas y escala alertas al asesor o supervisor responsable.

- **Empresa:** My SEO Company — Nicolás Navarro (`nicolas@myseocompany.co`)
- **Stack:** Laravel (PHP), MySQL, colas de trabajo (Jobs), MCP servers, API ARI CRM
- **Sprint activo:** Sprint 1 — septiembre 2026. Entrega MVP: noviembre 2026.

## Arnés operativo (leer antes de cualquier tarea comercial o estratégica)

El harness vive en `myseo-harness/`. Consultar en este orden:

1. `context/revenue-architecture/revenue-architecture-framework.md` — marco obligatorio para estrategias
2. `myseo-harness/SOUL.md` — rol y principios del agente
3. `myseo-harness/MYSEO.md` — método SCAN → CONFIGURE → INSTALL → LAUNCH → MONITOR
4. `myseo-harness/POLICIES.md` — qué requiere aprobación humana
5. `myseo-harness/SOURCES.md` — jerarquía de fuentes
6. `TASKS.md` y `DECISIONS.md` — estado operativo vigente
7. Workflow pertinente en `myseo-harness/workflows/`

## Ciclo de trabajo (SDLC)

| Fase | Archivo | Qué hace |
|------|---------|----------|
| SCAN | `workflows/01_scan.md` | Entender objetivo, datos, restricciones y línea base |
| CONFIGURE | `workflows/02_strategy.md` | Definir hipótesis, métricas, segmentos, responsables |
| INSTALL | `workflows/03_campaign.md` | Preparar automatizaciones, tracking y activos |
| LAUNCH | `workflows/04_launch.md` | Activar solo con aprobación humana explícita |
| MONITOR | `workflows/05_monitor.md` | Conciliar evidencia, medir, proponer ajustes |
| REPORT | `workflows/06_report.md` | Reportar hechos, inferencias y decisiones |

## Políticas críticas

- No activar acciones externas sin aprobación humana (campañas, mensajes, cambios en producción).
- No leer ni mostrar `.env`, claves, tokens ni credenciales.
- No incluir conversaciones reales ni datos personales en entregables; usar datos anonimizados.
- Cada cifra debe incluir fuente, periodo y método. Una conversación no es una venta.
- Clasificar toda iniciativa como **adquisición**, **retención** o **expansión** antes de proponer acciones.
- No escalar adquisición si la atención o el seguimiento son la restricción.

## Reglas para cambios de código

- Estudiar la arquitectura existente antes de proponer cambios.
- No implementar solo a partir de este documento; acordar el alcance primero.
- Los mecanismos de alerta y escalamiento deben ser configurables y validados con el mentor.
- Toda recomendación automatizada debe conservar evidencia suficiente para revisión humana.
- Respetar confidencialidad: no exponer datos reales de clientes en servicios públicos de IA.

## Archivos operativos de este repo

| Archivo | Propósito |
|---------|-----------|
| `TASKS.md` | Tareas activas con estado, responsable y siguiente acción |
| `DECISIONS.md` | Decisiones vigentes con fecha, motivo y evidencia |
| `MEMORY.md` | Aprendizajes permanentes con fuente y utilidad futura |
| `SOURCES.md` | Fuentes autorizadas con autoridad y restricciones |
| `GOAL.md` | Meta comercial vigente del cliente activo |
| `AGENTS.md` | Contexto extendido del proyecto (Codex y otros agentes) |

## Jerarquía cuando hay conflicto

`CLAUDE.md` > `AGENTS.md` > `myseo-harness/` > `context/`

Los hechos, decisiones, fuentes y tareas de este repositorio permanecen aquí y nunca se mezclan con otros clientes.
