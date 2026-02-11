# Machine Reporting API

Este documento describe el flujo de ingesta de producción y fallas desde PLCs hacia el CRM.

**Objetivo**
- Recibir reportes por minuto, con soporte de batch offline.
- Garantizar deduplicación e integridad.
- Registrar fallas y resets del tacómetro.

**Endpoint**
`POST /api/v1/machines/report`

**Autenticación**
- `Authorization: Bearer <token>`
- El token es único por máquina.
- El token se valida contra `machine_tokens.token_hash` (SHA-256).

**Integridad**
- Header: `X-Signature`
- Se calcula como HMAC-SHA256 del raw body usando el token en texto plano.
- Ejemplo:
  - `X-Signature = hash_hmac('sha256', RAW_BODY, TOKEN)`
- Se acepta también `sha256=<hex>`.

**Payload**
```json
{
  "serial": "PLC-00001234",
  "batch_id": "batch-2026-02-10-1",
  "reported_at": "2026-02-10T12:00:00Z",
  "reports": [
    {
      "minute_at": "2026-02-10T11:59:00Z",
      "tacometer_total": 120340,
      "units_in_minute": 5,
      "is_backfill": true,
      "faults": [
        {
          "code": "F-101",
          "severity": "high",
          "metadata": { "temp": 92 }
        }
      ]
    }
  ],
  "faults": [
    {
      "code": "F-202",
      "severity": "low",
      "reported_at": "2026-02-10T12:00:30Z",
      "metadata": { "note": "sensor jitter" }
    }
  ]
}
```

**Respuesta**
```json
{
  "report_id": 1,
  "summary": {
    "ingested": 1,
    "deduped": 0,
    "rejected": 0,
    "faults_ingested": 0,
    "anomalies": []
  }
}
```

**Idempotencia y dedupe**
- Se considera duplicado si ya existe `machine_id + minute_at` en `machine_production_minutes`.
- Los duplicados se cuentan en `summary.deduped`.

**Out-of-order / batch offline**
- Se aceptan minutos fuera de orden.
- Se insertan si no existen, marcando `is_backfill` si:
  - Viene explícito en el payload, o
  - El minuto es más viejo que 2 minutos respecto al `received_at`.

**Detección de reset del tacómetro**
- Si el `tacometer_total` baja respecto al último minuto anterior, se crea un evento:
  - `fault_code = TACOMETER_RESET`
  - `severity = info`
  - `metadata` incluye los valores anterior y actual.

**Validaciones de anomalías**
- `units_in_minute < 0` se rechaza.
- `units_in_minute` mayor al umbral máximo se rechaza.
- `minute_at` en el futuro (más de 120s) se rechaza.
- Saltos de tacómetro demasiado grandes se rechazan.
- Los rechazados se listan en `summary.anomalies`.

**Errores comunes**
- `401 Missing bearer token` si falta `Authorization`.
- `401 Invalid token` si el token no existe o está revocado.
- `401 Missing signature` si falta `X-Signature`.
- `401 Invalid signature` si el HMAC no coincide.
- `422 Serial does not match token` si el `serial` del payload no corresponde a la máquina del token.
