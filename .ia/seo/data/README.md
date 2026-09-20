# Inventario de datos — Maquiempanadas SEO

Exports crudos e inmutables. Cada carpeta de GSC incluye `Filtros.csv` con los filtros exactos con que se exportó. No editar los CSV; los análisis viven en `../analisis_keywords_gsc.md`.

## Estructura

```
google_ads/     Search terms + Auction insights (2026-01-20 → 2026-09-18)
gsc/            Google Search Console, propiedad maquiempanadas.com (2026-09-19)
  3_meses/      jun–sep 2026, por tipo de búsqueda: web / imagen / video
  16_meses/     ventana "16 meses" (¡con hueco!, ver abajo), por tipo de búsqueda
  countries/    Filtrado por país (usa_16_meses, co_16_meses) × tipo de búsqueda
youtube/        Analytics del canal "Maquina Empanadas Maquiempanadas Colombia"
                (2011-07 → 2026-09): Contenido, Fuente de tráfico, Ubicación geográfica
                — SIN USAR todavía en el análisis
Coverage…/      Drilldown de Cobertura (slice: "Página alternativa con canónica adecuada", 1.000 URLs)
…Generative-AI…/ Funciones de IA generativa, 3m (solo impresiones, sin clics)
…Video-indexing…/ 37 videos con incidencia "el vídeo no está en página de visualización"
```

## Archivos especiales

- `gsc/3_meses/web/Gráfico_diario.csv` — serie **diaria** (92 filas) preservada del primer export; el re-export `Gráfico.csv` solo trae 4 filas mensuales. Única fuente con granularidad diaria.

## Advertencias de uso (verificadas en auditoría 2026-09-19)

1. **Caps de export.** `Consultas.csv` y `Páginas.csv` están limitados a 1.000 filas (long tail invisible). `Consultas.csv` además excluye las consultas anónimas.
2. **Discontinuidad 16m.** Sin datos entre 2025-06-22 y 2026-02-23 (~8 meses). La ventana efectiva es ~8-9 meses: no usar el acumulado "16m" para tendencias, solo como inventario de queries.
3. **No sumar `Páginas.csv` contra totales de `Gráfico`/`Países`.** Los deltas son bidireccionales según export: global web +28%/+51%, imagen +56%/+59%, AI +12%, USA web −11%, CO web −29%, USA imagen −22%. Los valores por fila sí son confiables; para cruzar dimensiones se necesita BigQuery/Looker.
4. **`video` en 3m está vacío** (0 impresiones): la actividad de video en Search es anterior a jun-2026 y totaliza 33 impresiones/16m.
5. **Google Ads — filas "Total:".** `Informe de términos de búsqueda.csv` incluye 9 filas de subtotal de Google (`Total: Cuenta`, `Total: Máximo rendimiento`, …) que **no son términos**. Filtrarlas antes de agregar: los términos reales son 3.433 únicos (9,17M COP / 928 conv.); las cifras de cuenta no salen de este reporte.
6. **YouTube.** Los .zip originales fueron eliminados tras verificar (md5) que las carpetas extraídas son idénticas.
