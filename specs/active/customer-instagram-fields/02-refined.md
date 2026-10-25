---
slug: customer-instagram-fields
refined_at: 2026-10-25
---

# Refined User Story

## Actor

Ejecuta: asesor comercial que abre la ficha de un prospecto en `/customers/{id}/show` y edita sus datos en `/customers/{id}/edit`.
Se beneficia: el mismo asesor (calificación rápida) y el supervisor de ventas (segmentación por tamaño de audiencia).

Actor secundario: el coordinador técnico, que debe poder desplegar la migración sobre una base donde `instagram_url` **ya existe**, sin que el deploy falle.

## Valor

Hoy el handle de Instagram de un prospecto vive fuera del CRM (hojas sueltas, notas libres). El asesor no puede juzgar si un prospecto es una panadería de barrio o una cadena con 80k seguidores sin salir del CRM. Guardar handle + seguidores como columnas propias permite:

- Calificar el tamaño del prospecto sin abrir Instagram manualmente (≈1-2 min por prospecto evitados).
- Filtrar/segmentar más adelante por audiencia (fuera de alcance aquí, pero habilitado).
- Evitar que el dato siga viviendo en `notes` como texto no consultable.

## Criterios de aceptación

- **AC1** — Given una base donde `customers.instagram_url` ya existe (producción), When se corre `php artisan migrate`, Then la migración no falla y no intenta recrear la columna.
- **AC2** — Given una base donde `customers.instagram_followers` no existe, When se corre `php artisan migrate`, Then se crea como `UNSIGNED BIGINT NULL` sin índice y sin default.
- **AC3** — Given un entorno donde la tabla `customers` no existe (CI/fresh), When se corre la migración, Then retorna sin error (guarda `Schema::hasTable`).
- **AC4** — Given un `Customer`, When se asignan `instagram_url` e `instagram_followers` por asignación masiva, Then ambos quedan en `$fillable` y `instagram_followers` se castea a entero.
- **AC5** — Given un customer con `instagram_url` guardado como handle suelto (`maquiempanadas`), como `@maquiempanadas` o como URL completa (`https://www.instagram.com/maquiempanadas/?igsh=x`), When se renderiza el detalle, Then el enlace apunta siempre a `https://instagram.com/maquiempanadas`.
- **AC6** — Given un customer con `instagram_followers` no nulo, When se renderiza el detalle, Then se muestra el número con separador de miles junto al enlace.
- **AC7** — Given un customer sin Instagram ni seguidores, When se renderiza el detalle, Then no aparece el bloque de Instagram (no se muestra una fila vacía).
- **AC8** — Given el formulario de edición, When el asesor guarda handle y seguidores, Then `CustomerController::update()` persiste ambos valores; seguidores vacío se guarda como `NULL`, no como `0`.

## Requisitos no funcionales

- **Performance**: la migración debe ser segura sobre ~147k filas. Dos `ADD COLUMN` nullable sin default y sin índice → en MySQL 8 son operaciones `INSTANT`/online, sin reescritura de tabla ni bloqueo prolongado. No se añade ningún índice.
- **Seguridad**: `instagram_url` se renderiza dentro de un `href`; se normaliza a `https://instagram.com/<handle>` construido desde el handle extraído, nunca se vuelca el valor crudo en el atributo, de modo que un valor con `javascript:` o con otro dominio no puede inyectarse. El texto visible se escapa con `{{ }}`. Enlace con `target="_blank"` + `rel="noopener"`.
- **Multi-tenancy**: no aplica — `customers` es tabla única de este tenant (Maquiempanadas), sin `tenant_id`.
- **Observabilidad**: la escritura queda cubierta por el `static::updated` ya existente en `Customer`, que registra un `ActivityLog` `customer.updated` con los nombres de campos cambiados. No se añade logging nuevo.
- **i18n / accesibilidad**: etiquetas en español ("Instagram", "seguidores"), consistentes con el resto de la ficha. El enlace lleva texto visible (`@handle`), no solo un icono.
- **Compatibilidad**: la ficha `show.blade.php` usa Tailwind (`layouts.tailwind`); el formulario de edición usa Bootstrap (`layout`). Cada cambio respeta el framework de su vista.

## Supuestos

- [CONFIRMAR] `instagram_url` guarda el perfil del prospecto (handle o URL), no un enlace a un post concreto. La normalización asume perfil: `instagram.com/p/<id>` no se trata de forma especial.
- [CONFIRMAR] `instagram_followers` es un snapshot manual, sin fecha de captura ni histórico. Si el negocio necesita saber "seguidores a qué fecha", hace falta una columna adicional que no está en este alcance.
- [CONFIRMAR] La columna `instagram_url` creada hoy en producción es exactamente `VARCHAR(255) NULL` sin default; la migración la declara igual para que entornos nuevos queden idénticos a producción.
- [CONFIRMAR] No se requiere el campo en el formulario de **creación** de prospecto (`customers/create`), solo en edición y detalle.

## Riesgos

- **Blast radius**: bajo. Dos columnas nullable nuevas, un bloque de UI condicional y dos asignaciones en `update()`. Ningún contrato existente cambia. El riesgo real concentrado es el deploy de la migración sobre producción con la columna ya presente — mitigado por las guardas `hasTable`/`hasColumn`.
- **Reversibilidad**: alta. `down()` elimina solo las columnas que existan. Advertencia: `down()` borraría también `instagram_url`, incluida la que creó el coordinador a mano y los datos que ya tenga. Un rollback debe ser decisión explícita, no automática.
- **Dependencias externas**: ninguna. No se consulta la API de Instagram; los dos campos se cargan a mano o por importación.
- **Impacto en tenants activos**: tenant único (Maquiempanadas). Los ~147k customers existentes quedan con ambos campos en `NULL` y la ficha se ve igual que hoy.

## Fuera de alcance

- Scraping o sincronización automática de seguidores desde Instagram.
- Histórico de seguidores en el tiempo (una sola columna, un solo valor).
- Filtro o segmentación por seguidores en el índice de customers.
- Campo de Instagram en el formulario de creación (`customers/create`) y en el alta por API.
- Mostrar Instagram en el panel lateral del índice (`index_partials/customer_header.blade.php`).
- Validación de unicidad del handle o detección de duplicados por Instagram.
