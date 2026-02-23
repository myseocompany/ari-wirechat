# Auditoría técnica: `actions:notify` (pendientes `due_date` + `type_id = 9`)

## 1) Comando operativo esperado

```bash
php8.3 /home/forge/arichat.co/artisan actions:notify
```

## 2) Hallazgo clave del scheduler

En este repositorio, `actions:notify` **no está registrado en el Scheduler de Laravel** (`routes/console.php`).
Por tanto, para que funcione en producción debe existir **cron del sistema** ejecutando el comando directo.

Ejemplo recomendado:

```cron
* * * * * php8.3 /home/forge/arichat.co/artisan actions:notify >> /dev/null 2>&1
```

## 3) Flujo funcional auditado (lo que debe pasar)

1. Se leen configuraciones desde tabla `configs` vía `app_config(...)`.
2. Se ejecutan recordatorios WhatsApp para reuniones (`actions.type_id = 9`):
   - `delivery_date IS NULL`
   - `due_date IS NOT NULL`
   - Ventana de 60 min / 10 min (`whereBetween`)
   - Ventana morning (`whereDate(due_date, hoy)` en franja de 1 minuto)
3. Se evita reenvío si ya existe acción `type_id = 16` con mismo `object_id` y `reminder_type`.
4. Se envía plantilla por `WhatsAppService`.
5. Se registra traza como acción `type_id = 16` para deduplicación futura.

Nota: el mismo comando también tiene un bloque de email para pendientes, pero ese bloque no filtra `type_id = 9`.

## 4) Tablas obligatorias para este flujo

## 4.1 Tabla `actions`

Columnas mínimas para este caso:
- `id`
- `customer_id`
- `type_id`
- `due_date`
- `delivery_date`
- `url`
- `object_id`
- `notified_at`
- `reminder_type`
- `deleted_at`

Índices recomendados:
- `actions_notified_at_index` (`notified_at`)
- `actions_type_object_reminder_deleted_idx` (`type_id`, `object_id`, `reminder_type`, `deleted_at`)
- Índices sobre `type_id` y `customer_id`

## 4.2 Tabla `action_types`

Debe tener, mínimo, estos IDs de negocio:
- `id = 9` (tipo reunión a notificar por WhatsApp)
- `id = 16` (registro técnico de reminder enviado/fallido)

## 4.3 Tabla `customers`

Columnas mínimas:
- `id`
- `user_id`
- `phone` / `phone2` (el modelo calcula teléfono con `getPhone()`)

## 4.4 Tabla `users`

Columnas mínimas:
- `id`
- `email` (usado por el bloque email del mismo comando)

## 4.5 Tabla `configs`

Claves mínimas esperadas para reuniones:
- `followup_default_minutes`
- `whatsapp_meeting_reminder_60_template`
- `whatsapp_meeting_reminder_10_template`
- `whatsapp_meeting_reminder_morning_template`
- `whatsapp_meeting_reminder_60_minutes`
- `whatsapp_meeting_reminder_10_minutes`
- `whatsapp_meeting_reminder_morning_time`

## 4.6 Tablas de WhatsApp

`whatsapp_accounts`:
- `id`, `api_url`, `api_token`, `is_default`

`whatsapp_templates`:
- `id`, `whatsapp_account_id`, `name`, `language`

## 5) Controladores, rutas y vistas que deben estar presentes

## 5.1 Obligatorio para ejecución de `actions:notify`

No depende de controlador ni vista para correr en cron.
Depende de:
- Comando console registrado y disponible
- Servicio de WhatsApp
- Modelos/relaciones

## 5.2 Obligatorio para links en notificación

El comando genera URL con `route('customers.show', $customerId)`.
Por tanto, deben existir:
- Ruta nombrada `customers.show`
- Método controlador que responda esa ruta
- Vista de detalle del cliente

## 5.3 Artefactos que deben existir (checklist de código)

- `app/Console/Commands/NotifyPendingActions.php`
- `bootstrap/app.php` (registro de `NotifyPendingActions`)
- `app/Services/WhatsAppService.php` (`sendTemplateToCustomer` + log `type_id = 16`)
- `app/Models/Action.php`
- `app/Models/Customer.php` (relación `user` y método `getPhone()`)
- `app/Models/Config.php`
- `app/Support/helpers.php` (`app_config`)
- `routes/web.php` (ruta `customers.show`)
- `app/Http/Controllers/CustomerController.php` (`show`)
- `resources/views/customers/show.blade.php`
- `resources/views/customers/readonly.blade.php`

## 6) SQL de auditoría (ejecutar en producción)

## 6.1 Existencia de tablas

```sql
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'actions',
    'action_types',
    'customers',
    'users',
    'configs',
    'whatsapp_accounts',
    'whatsapp_templates'
  );
```

## 6.2 Validar columnas críticas de `actions`

```sql
SELECT column_name
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'actions'
  AND column_name IN (
    'id',
    'customer_id',
    'type_id',
    'due_date',
    'delivery_date',
    'url',
    'object_id',
    'notified_at',
    'reminder_type',
    'deleted_at'
  );
```

## 6.3 Validar catálogo de tipos

```sql
SELECT id, name
FROM action_types
WHERE id IN (9, 16);
```

## 6.4 Validar configuración mínima

```sql
SELECT `key`, `value`, `type`
FROM configs
WHERE `key` IN (
  'followup_default_minutes',
  'whatsapp_meeting_reminder_60_template',
  'whatsapp_meeting_reminder_10_template',
  'whatsapp_meeting_reminder_morning_template',
  'whatsapp_meeting_reminder_60_minutes',
  'whatsapp_meeting_reminder_10_minutes',
  'whatsapp_meeting_reminder_morning_time'
);
```

## 6.5 Validar candidatos actuales a notificación (`type_id = 9`)

```sql
SELECT id, customer_id, due_date, delivery_date, url
FROM actions
WHERE type_id = 9
  AND due_date IS NOT NULL
  AND delivery_date IS NULL
ORDER BY due_date ASC
LIMIT 50;
```

## 6.6 Validar deduplicación (acciones técnicas `type_id = 16`)

```sql
SELECT object_id, reminder_type, COUNT(*) AS total
FROM actions
WHERE type_id = 16
GROUP BY object_id, reminder_type
HAVING COUNT(*) > 1
ORDER BY total DESC
LIMIT 50;
```

## 7) Criterio de aprobación de auditoría

`OK` cuando se cumple todo:
- Existe cron ejecutando `php8.3 /home/forge/arichat.co/artisan actions:notify`.
- Existen tablas y columnas críticas listadas.
- Existen `action_types` 9 y 16.
- Existen claves `configs` requeridas con valores válidos.
- Existe ruta `customers.show` y su vista.
- Se generan acciones `type_id = 16` al enviar reminder.
- No hay duplicados excesivos por (`object_id`, `reminder_type`) en `type_id = 16`.

## 8) Evidencia en código (este repo)

- `actions:notify` existe y está registrado como comando.
- No está agendado en `routes/console.php` (solo `retell:process --limit=200`).
- Flujo `type_id = 9` para WhatsApp está implementado con deduplicación por `type_id = 16`.
