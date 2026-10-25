---
slug: customer-instagram-fields
title: Campos de Instagram en Customer
status: applied
created: 2026-10-25
author: nicolas@myseocompany.co
---

# User Story (raw)

Como asesor comercial, quiero ver el Instagram y la cantidad de seguidores de un prospecto en su ficha del CRM, para calificar su tamaño y relevancia antes de contactarlo.

## Contexto crudo

> El coordinador ya creó HOY la columna `instagram_url` (VARCHAR(255) NULL, sin posición específica) directamente en producción vía `Schema::table` porque se necesitaba con urgencia. **Ese cambio no está en el código.** Tu trabajo es hacerlo como toca en el codebase, más el campo de seguidores.
>
> 1. Migración `add_instagram_fields_to_customers_table`:
>    - `instagram_url` VARCHAR(255) NULL — CONDICIONAL: ya existe en producción; usa `if (!Schema::hasColumn(...))` para que no explote al desplegar.
>    - `instagram_followers` UNSIGNED BIGINT NULL — no existe aún; también condicional por consistencia.
> 2. Modelo `Customer`: haz que los dos campos sean asignables/visibles según las convenciones del repo.
> 3. UI: muestra el Instagram como enlace (`https://instagram.com/<handle>`) y los seguidores en el detalle del customer, donde conviva con los campos de contacto/negocio.
> 4. Tests según las convenciones del repo.
> 5. SPECBOOT si el ciclo aplica.
> 6. Commit en la rama de este worktree. Sin push, sin deploy.
>
> Reglas: NO tocar producción (nada de SSH, nada de correr la migración contra la base real). La migración debe ser segura en una tabla de 147k filas (columnas nullable, sin índices nuevos innecesarios).

## Enlaces

- Conversación / ticket / issue de origen: pedido directo en sesión del 2026-10-25.
- Personas involucradas: coordinador (aplicó `instagram_url` en producción), Nicolás Navarro (solicitante).
