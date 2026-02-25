# /customers/{cid}/show: histories + timeline (guia para LLM)

## Objetivo
Explicar la parte inferior de la vista de cliente (`/customers/{cid}/show`), donde aparecen:
- la **Linea de tiempo del cliente** (`timeline`)
- el bloque de **Historial** (`customer_histories`)

Ambos usan datos de historial, pero con objetivos y presentacion diferentes.

## Ruta y flujo principal
1. Ruta: `GET /customers/{customer}/show`
2. Controlador: `CustomerController@show`
3. Vista: `resources/views/customers/show.blade.php`
4. En la columna central, la vista carga tabs con:
   - `customers.show_partials.actions_widget_wp` (timeline unificado)
   - `customers.show_partials.history` (historial detallado)

## Archivos involucrados
- `routes/web.php`
- `app/Http/Controllers/CustomerController.php`
- `resources/views/customers/show.blade.php`
- `resources/views/customers/partials/customer_tabs.blade.php`
- `resources/views/customers/show_partials/actions_widget_wp.blade.php`
- `resources/views/customers/show_partials/history.blade.php`
- `app/Models/Customer.php`
- `app/Models/CustomerHistory.php`

## Datos que llegan desde `CustomerController@show`
La accion `show($id)` prepara, entre otros:
- `$model` (cliente actual)
- `$actions` (acciones del cliente)
- `$histories` (snapshots de `customer_histories`, ordenados DESC por `updated_at`)
- `$historyOwnerMap` (mapa para detectar cambios de owner comparando snapshots consecutivos)
- `$chatMessages` + labels de origen (mensajes WireChat para timeline)

## Como se arma la parte inferior
En `customer_tabs.blade.php`, dentro de la seccion `customer-actions`, se renderiza:
1. Formulario de acciones
2. `actions_widget_wp` (timeline)
3. `history` (historial)

Eso significa que en pantalla ves:
- primero un timeline consolidado (acciones + cambios de estado/asignacion + chat)
- luego un listado de historial enfocado en snapshots de cliente

## Timeline (`actions_widget_wp`) - logica
Se construye una coleccion `$timeline` y se agregan eventos de 3 fuentes:

1. **Acciones** (`$actions`)
- Cada accion entra como `type = action`
- Incluye nota, creador, color/icono por tipo, pendiente, transcripcion, etc.

2. **Histories del cliente** (`$customer->histories`)
- Se recorren en orden cronologico ASC (`sortBy('updated_at')`)
- Siempre se agrega un evento `type = estado`
- Si cambia `user_id` respecto al snapshot previo, se agrega tambien `type = asignacion`

3. **Chat messages** (`$chatMessages`)
- Se agrega `type = chat` con direccion entrante/saliente y etiqueta de canal

Finalmente, todo se ordena DESC por fecha para mostrar lo mas reciente arriba.

## Historial (`history`) - logica
Este bloque no mezcla acciones: prioriza snapshots de cliente.

1. Crea un item especial `type = current` usando el estado actual de `$model`.
2. Convierte cada registro de `$histories` en `type = history`.
3. Renderiza ambos en una sola lista (`current` + snapshots).
4. Usa `$historyOwnerMap` para marcar cuando hubo reasignacion de owner.

Cada fila muestra:
- fecha/hora
- owner (o sin asignar)
- usuario que actualizo (`updated_user`)
- estado y color del estado

## Relacion entre `customer_histories` y el timeline
`customer_histories` alimenta dos vistas al mismo tiempo:

1. **Timeline**:
- lo usa para construir eventos de negocio (`estado` y `asignacion`)
- combinado con acciones y chat para dar contexto operacional completo

2. **Historial**:
- lo usa como evidencia detallada de snapshots del cliente
- junto con un item extra de "Estado actual"

Por eso parece "duplicado": no lo es; son dos lecturas distintas del mismo origen.

## Fuente de verdad del historial
El modelo `CustomerHistory` guarda snapshots del cliente con `saveFromModel($model)`.
Ese guardado se invoca en varios flujos (ej. update, cambio de estado desde accion, etc.) antes de modificar el cliente, para conservar el estado previo.

## Nota tecnica importante para LLM
- En `show_partials/history.blade.php` existe una rama para `type = chat`, pero en esta vista no se inyecta `timelineItems` con chats.
- En la practica, los chats visibles en `/customers/{cid}/show` salen de `actions_widget_wp` (timeline), no del bloque `history`.

## Resumen mental para un LLM
Si necesitas explicarlo rapido:
- `/customers/{cid}/show` tiene abajo dos bloques.
- Ambos usan `customer_histories`.
- `actions_widget_wp` convierte histories en eventos (`estado/asignacion`) y los mezcla con `actions` y `chat`.
- `history` muestra snapshots de cliente (incluyendo estado actual) con detalle de owner/editor/status.
