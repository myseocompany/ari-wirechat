# Retomar landing máquinas de empanadas

Contexto del trabajo:
- Archivo principal: `public/educacion/index.html`
- Landing objetivo: `https://maquiempanadas.com/videos-fotos/`
- Se agregó una sección nueva entre testimonios (`#prueba-social`) y formulario (`#quiz`) llamada `Nuestras máquinas de empanadas: 3 modelos para cada negocio`.
- No se deben mover ni reescribir hero, videos, testimonios, FAQ ni formulario.

Estado actual:
- La sección incluye 3 cards: `MQE CM06`, `MQE CM05i`, `MQE CM08`.
- La card central `CM05i` estaba superponiéndose por `transform: scale(1.03)`. Ya se quitó el `scale` y se dejó destacada con borde/sombra.
- Se reemplazaron los placeholders `[XXX]` con datos encontrados en `.ia/portafolio_productos.md` y en páginas de producto públicas.
- El JSON-LD ya fue actualizado a `ItemList` con 3 `Product`.
- Se agregaron placeholders WebP en:
  - `public/images/machines/cm06.webp`
  - `public/images/machines/cm05i.webp`
  - `public/images/machines/cm08.webp`

Fuentes usadas para specs:
- `.ia/portafolio_productos.md`
- CM06: `https://maquiempanadas.com/product/maquina-para-empanadas-y-arepas-cm06/`
- CM05i: `https://maquiempanadas.com/product/maquina-para-contar-empanadas-por-internet-cm05i/`
- CM08: `https://maquiempanadas.com/product/maquina-para-empanadas-y-arepas-mixta-cm08-producto/`

Datos aplicados:
- CM06:
  - Capacidad: más de 300 empanadas/hora con 1 operario; 500 con 2 operarios.
  - Productos: empanadas de maíz y arepas.
  - Dimensiones: 60 x 60 x 60 cm.
  - Peso: 50 kg.
  - Precio mostrado: Desde USD 4.133.
- CM05i:
  - Capacidad: 750 a 1.600 empanadas/hora con 2 operarios.
  - Productos: empanadas de trigo, maíz, arepas, arepas rellenas, pupusas, patacones, tostones, aborrajados y pasteles.
  - Voltaje: 220V.
  - Dimensiones: 160 x 76 x 90 cm.
  - Peso: 95 kg.
  - Precio mostrado: Consulta precio actualizado.
- CM08:
  - Capacidad: 300 a 500 unidades/hora con 1 o 2 operarios.
  - Productos: empanadas de maíz, trigo, verde, arepas rellenas, tostones, patacones, pupusas, aborrajados y pasteles.
  - Dimensiones: 70 x 70 x 70 cm.
  - Peso: 78 kg.
  - Precio mostrado: Desde USD 5.798.

Tracking:
- Cada CTA usa clase `js-machine-card-click`.
- El script solo hace `window.dataLayer.push(...)` si `window.dataLayer` ya existe; no crea un dataLayer nuevo.
- Evento:
  - `event`: `machine_card_click`
  - `event_name`: `machine_card_click`
  - `machine_model`: `CM06`, `CM05i`, `CM08`
  - `click_type`: `whatsapp` o `details`

Pendiente al retomar:
1. Revisar visualmente en navegador real que las cards ya no se superponen.
2. Si siguen apretadas en desktop, cambiar `.machines-grid` en `@media (min-width: 1024px)` de 3 columnas a:
   `grid-template-columns: repeat(3, minmax(0, 1fr));`
   y aumentar `gap` a `20px` o reducir padding/texto en cards.
3. Verificar mobile: debe ser una columna, sin superposición ni overflow horizontal.
4. Confirmar si el precio de CM05i debe mostrarse como `Desde USD 13.329` o seguir como `Consulta precio actualizado`.
5. Sustituir imágenes placeholder por fotos reales optimizadas WebP cuando el equipo las entregue.
6. Correr:
   - `vendor/bin/pint --dirty`
   - Validación JSON-LD con un parseo rápido.

Prompt para retomar:

```
Estamos trabajando en `/Users/nicolasnavarrorincon/projects/ari-wirechat`, archivo `public/educacion/index.html`.

Retoma desde `.ia/retomar_landing_maquinas.md`.

Necesito terminar la sección nueva `Nuestras máquinas de empanadas` de la landing `https://maquiempanadas.com/videos-fotos/`.

Prioridades:
1. Verifica y corrige que las 3 cards de máquinas no se superpongan en desktop ni mobile.
2. Mantén intactos hero, videos, testimonios, FAQ y formulario.
3. Conserva los datos reales ya cargados para CM06, CM05i y CM08.
4. Revisa JSON-LD `ItemList` con 3 `Product`.
5. Verifica eventos `machine_card_click`.
6. Corre `vendor/bin/pint --dirty`.
7. Entrega diff, resumen visual desktop/mobile y cualquier pendiente.
```
