# Cotización Funcional - Módulo de Telemetría de Máquinas

## 1) Alcance de esta cotización
Esta cotización aplica **únicamente** al módulo de telemetría de máquinas.

## 2) Objetivo del módulo
Construir y/o consolidar un módulo web para monitorear operación de máquinas en planta, con:
1. Ingesta segura de telemetría.
2. Trazabilidad de producción y fallas.
3. Gestión administrativa de máquinas y tokens.
4. Visualización operativa para usuario final (app de piso/planta).

## 3) Backend incluido (Telemetría de máquinas)

## 3.1 Administración de máquinas
1. CRUD de máquinas (`serial`, cliente actual, última conexión).
2. Emisión y revocación de tokens por máquina.
3. Historial de asignación de máquina a cliente.

## 3.2 API de telemetría
1. Endpoint de recepción de reportes de máquina.
2. Autenticación por `Bearer token` por máquina.
3. Validación de firma HMAC (`X-Signature`) para integridad del payload.
4. Validación de consistencia entre token y serial reportado.
5. Registro de lotes (batch) y timestamp de recepción.

## 3.3 Reglas de datos operativos
1. Registro de producción por minuto.
2. Dedupe por combinación `machine_id + minute_at`.
3. Soporte de reportes fuera de orden / backfill offline.
4. Registro de eventos de falla.
5. Detección de anomalías operativas (ej. resets de tacómetro, valores inválidos).

## 3.4 Persistencia y trazabilidad
1. Tablas de máquinas, tokens, reportes, minutos de producción y fallas.
2. Auditoría de actividad operativa por máquina.
3. Consulta histórica en panel admin.

## 4) Frontend de usuario final incluido en la cotización

La cotización **sí incluye** el frente visible para el operador/supervisor de planta tomando como base la app demo **Empanada Flow**.

Ruta base actual de la app: `/empanadaflow/`

La entrega quedará publicada como un link dentro de la web `maquiempanadas.com`, apuntando a `/empanadaflow/`.

## 4.1 Qué contiene hoy la demo (base funcional)
1. Vista Home con estado de máquina, métricas del día y pulso de producción.
2. Vista Producción con carga manual (+1, +10, +50), filtros día/semana/mes y resumen 7 días.
3. Vista Paros (downtime) para iniciar/finalizar parada y registrar motivo.
4. Vista Operador para iniciar/finalizar sesión y comparar desempeño por operador.
5. Navegación móvil por pestañas y soporte de idioma EN/ES.

## 4.2 Alcance para llevar la demo a versión productiva
1. Conectar la app a APIs reales de telemetría (en lugar de estado local demo).
2. Persistir eventos en backend (producción, paros, sesiones de operador).
3. Consumir métricas reales por máquina/turno/rango de tiempo.
4. Mantener UI móvil como experiencia principal de operación.

## 5) Integraciones incluidas
1. Integración PLC/dispositivo -> API de reportes de máquina.
2. Integración app de usuario final Empanada Flow -> backend de telemetría.

## 6) Exclusiones explícitas (fuera de esta cotización)
1. Módulos comerciales, de marketing o ventas no relacionados con telemetría de máquinas.
2. Integraciones de mensajería y voz no requeridas por el flujo de telemetría.
3. Personalizaciones no relacionadas con telemetría de máquinas.

## 7) Entregables de la cotización
1. Documento de alcance funcional del módulo de máquinas.
2. Backend operativo de telemetría (admin + API + reglas de negocio).
3. App de usuario final Empanada Flow conectada a backend productivo.
4. Pruebas funcionales clave de ingesta y trazabilidad.
5. Guía técnica corta de despliegue y operación del módulo.

## 8) Supuestos
1. El cliente/proyecto provee credenciales o conectividad de dispositivos que reportan datos.
2. Se define ambiente de despliegue (staging/producción) y responsables de infraestructura.
3. El alcance visual parte de la app demo existente; cambios de UX mayores se cotizan aparte.

## 9) Nota de alcance
Esta propuesta se limita al vertical de **telemetría de máquinas + app operativa de planta**.
