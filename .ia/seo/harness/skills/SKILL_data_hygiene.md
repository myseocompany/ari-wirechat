---
skill: data_hygiene
version: 1.0
---

# SKILL — Data Hygiene

Reglas de manejo de datos crudos para el proyecto SEO. Aplica a DataForSEO, GSC, Ads y YouTube.

## Estructura obligatoria de datos

```
.ia/seo/data/
├── dataforseo/
│   ├── YYYY-MM-DD_<endpoint>_<slug>.json   ← respuesta cruda con timestamp
│   ├── COSTS.csv                            ← una fila por llamada con costo real
│   └── README.md                            ← inventario y advertencias
├── gsc/                                     ← exports de Search Console
├── google_ads/                              ← search terms + auction insights
└── youtube/                                 ← analytics del canal
```

## Reglas duras

1. **Nada de datos crudos editados a mano.** Los JSON/CSV de fuentes externas son inmutables. Cualquier transformación va a un archivo derivado con nombre distinto.

2. **Timestamp en el nombre.** Todo archivo derivado incluye fecha (`YYYY-MM-DD`) en su nombre o en frontmatter. Sin fecha, no se sabe si el análisis está vigente.

3. **Comando exacto documentado.** Todo CSV/MD derivado empieza con un bloque de metadata:
   ```
   ---
   generated_from: [lista de archivos fuente]
   generated_at: 2026-09-20
   script: .ia/seo/scripts/gap_analysis.py
   args: {location_code: 2840, threshold: 0.5}
   ---
   ```

4. **Costos registrados.** Cada llamada a DataForSEO va con su fila en `COSTS.csv`:
   ```
   date,endpoint,keyword_or_target,cost_usd,response_file
   2026-09-19,keywords_data/google_ads/search_volume/live,batch_30_kw_usa_es,0.09,volume_usa_es.json
   ```

5. **Reglas de clusters publicadas.** Cualquier CSV que agrupe keywords por cluster debe declarar la regla exacta (regex o substring) en su frontmatter. Reglas no reproducibles → auditoría rechaza el output.

## Reglas de análisis

- **No mezclar denominadores.** Impresiones por país (Países.csv) y por URL (Páginas.csv) no cuadran entre sí en GSC — no sumar como si compartieran base.
- **Documentar gaps del export.** GSC limita a 1,000 filas por dimensión. El límite se declara explícitamente en cualquier análisis.
- **Discontinuidades explícitas.** El gráfico "16 meses" de GSC tiene ~8 meses de hueco entre 2025-06 y 2026-02. No usar el acumulado como tendencia sin declarar el hueco.
- **Rank_group vs rank_absolute.** En DataForSEO SERP, `rank_group` es la posición orgánica; `rank_absolute` incluye features (imgs, video, PAA). No comparar entre sí ni con la "posición promedio" de GSC.

## Reglas de compartición

- **Reportes al cliente** salen desde `reporte_maquiempanadas.html` (o versión sucesiva). Nunca se comparte un CSV crudo sin resumen ni contexto.
- **Datos sensibles** (leads, precios negociados, contratos) no salen del `.ia/seo/data/` bajo ninguna circunstancia. Si aparecen accidentalmente en un CSV, se sanitizan antes de mergear.
- **Credenciales** viven exclusivamente en `myseo-env/.env`. Nunca se copian a `.ia/seo/` ni se logean en `COSTS.csv`.

## Backups

- Cada snapshot de monitoreo semanal se preserva en `.ia/seo/monitoring/YYYY-WW/`. Nunca se sobrescribe.
- Los datos crudos de DataForSEO son fuente de verdad y se conservan indefinidamente. Si se depura por espacio, se archiva primero en un tarball con manifiesto.
