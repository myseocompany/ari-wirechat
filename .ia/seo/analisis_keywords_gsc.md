# Análisis SEO — Maquiempanadas (v7)

**Fecha:** 2026-09-19
**Fuente:** Exports de GSC, Google Ads y YouTube (2026-09-19) — inventario completo y advertencias en `data/README.md`
**Analista:** Nicolás Navarro (MySEO) + Claude
**Historial de revisiones:**
- v1-v5 objetadas sucesivamente por ChatGPT.
- **v6** integra 3 hallazgos nuevos: discrepancia Páginas.csv vs Países.csv, Google Ads corriendo, 37 videos con incidencia. Corrige blog 20% (no 15%).
- **v7** corrige el **error grave de la sección Ads en v6** (confundí filas de subtotales `Total: …` del export con datos de campañas y términos). Añade análisis de datasets antes ignorados: consultas de imagen (global y USA), páginas de imagen, dispositivos imagen, consultas de video, Colombia web, tendencia mensual AI. Corrige inconsistencias menores (36→37 videos, laminadora ausente en tabla 16m).

## 🚨 Corrección crítica de v7 — sección Google Ads

En v6, la sección "7. Google Ads" trató las **9 filas `Total: …`** del export como si fueran datos de campaña y términos. Son **subtotales agregados por Google** que enmascaran el tráfico no revelado por término (típico en PMax/Demand Gen). Números correctos:

| Métrica | v6 (mezclando subtotales) | v7 (solo términos revelados) |
|---|---:|---:|
| Términos únicos | 6,017 | **3,424** |
| Filas de términos | — | 6,008 |
| Costo | 63.4M COP (≈$15,101 USD) | **9.17M COP (≈$2,183 USD)** |
| Clics | 138,628 | **9,227** |
| Impresiones | 1,574,138 | **53,422** |
| Conversiones | 7,289 | **927** |
| CPA | $2 USD | **$2.36 USD** |
| "Marca ≈60% conv" | 6.9% (506/7,289) — mal denominador | **54.6% (506/927)** — correcto direccionalmente |
| `maquina para hacer empanadas` conv | 40 | **54** (10 filas × 4 campañas) |
| `...colombia` conv | 8 | **20.5** |

Los datos por campaña etiquetada (las 7 reales) **siguen siendo correctos** — el problema estaba en presentarlos como si el total de la cuenta fuese 63.4M COP. La "campaña" ficticia "PMax master 9 agrupados" que v6 reportó **no existe**: eran las 9 filas de subtotal de Google.

La sección detallada abajo (sección 7) queda actualizada con estos números.

---

## Hallazgos nuevos de la segunda auditoría (v6)

### 1. Integridad de datos: Páginas.csv ≠ Países.csv en la misma carpeta

| Periodo | Tipo búsqueda | Países.csv (impresiones) | Páginas.csv (impresiones) | Δ |
|---|---|---:|---:|---:|
| 3 meses | Web | 168,792 | 216,029 | **+28%** |
| 16 meses | Web | 512,176 | 772,796 | **+51%** |
| 3 meses | Imagen | 54,135 | 86,103 | **+59%** |
| 16 meses | Imagen | 165,165 | 257,516 | **+56%** |

Los CSVs de un mismo export **no cuadran entre sí**, incluso con **filtros idénticos verificados**. El re-export por tipo de búsqueda descartó la hipótesis inicial (filtro distinto). La discrepancia es **intrínseca a la agregación de GSC** — probablemente porque una impresión puede contarse en múltiples URLs (canonical, redirects, o cuenta anónima de rich results en Páginas pero no en Países).

**Consecuencia:** cualquier % que use Páginas como denominador es incierto. Cifras de páginas individuales siguen siendo correctas, pero **no se pueden sumar como si compartieran denominador con Países**. Para cruzar dimensiones con seguridad → BigQuery export o Looker.

### 2. Google Ads corriendo activamente (visible en Coverage)

- **51 URLs** con parámetro `gclid=` en el reporte de Coverage
- **7 URLs** con `utm_medium=paid`
- **Campaña identificada:** `gad_campaignid=23069071844`
- Ejemplo: `https://maquiempanadas.com/en/?gad_source=2&gad_campaignid=23069071844&gclid=...`

**Implicaciones:**
- La cliente **ya invierte en Google Ads dirigido a `/en/`** — el mercado anglófono es una apuesta activa, no una hipótesis.
- El **search terms report de Google Ads** es una fuente **gratuita** de keywords comerciales **con atribución a conversión**. Priorizar por revenue con Ads > priorizar por volumen con DataForSEO.
- Sube a la lista de bloqueantes: acceso al panel de Ads para exportar search terms.

### 3. Video indexing con 37 incidencias

El reporte `maquiempanadas.com-Video-indexing-Drilldown-2026-09-19/Tabla.csv` contiene **37 videos** con incidencia "El vídeo no está en una página de visualización" ([ref oficial Google](https://support.google.com/webmasters/answer/12472948)).

GOAL.md pide analizar SERP de video. Resolver este issue es **arreglo técnico barato** — probablemente markup schema.org VideoObject faltante o página incorrectamente marcada. Sube a candidatas Fase 1.

### 4. Vista universal completa (Web + Imagen + Vídeo)

GOAL.md pide análisis universal. Los exports por tipo de búsqueda (Web / Imagen / Vídeo) ya están en `.ia/seo/data/gsc/`.

**Totales 16 meses por tipo (Países.csv como fuente autoritativa):**

| Tipo | Clics | Impresiones | Share impr | Nota |
|---|---:|---:|---:|---|
| Web | 15,052 | 512,176 | 75.6% | Ya analizado en detalle arriba |
| **Imagen** | **1,375** | **165,165** | **24.4%** | **Pos promedio 21-36 — muy mal posicionado** |
| Vídeo | 1 | 33 | 0.005% | Prácticamente inexistente en Google Search |

**Imágenes por país (16m):**

| País | Clics | Impresiones | Posición promedio |
|---|---:|---:|---:|
| Colombia | 586 | 50,983 | 21.61 |
| Estados Unidos | 140 | 20,449 | **36.54** |
| España | 83 | 6,793 | 24.40 |
| Panamá | 62 | 3,959 | 24.17 |
| Chile | 56 | 6,860 | 26.92 |

**Hallazgos:**
- **Imágenes es 24% de la exposición del sitio pero rankea en posición 20-36** — enorme oportunidad. Para producto físico (maquinaria), la búsqueda de imágenes es especialmente relevante en fase de descubrimiento.
- **USA en imágenes está peor posicionado que Colombia** (36.54 vs 21.61) — probable causa: nombres de archivo/alt-text en español, sin versión inglesa.
- **Vídeo en Google Search prácticamente no existe** (33 impresiones en 16m) a pesar de que Coverage muestra 37 videos indexados. Consistente con el issue "el vídeo no está en una página de visualización" — el markup de video no está siendo reconocido correctamente por Google.

**Implicaciones para candidatas Fase 1:**
- El arreglo de video indexing puede desbloquear un tipo de búsqueda entero que hoy es prácticamente 0.
- Auditoría de imágenes (alt-text, filenames, structured data, tamaños) sube de prioridad — 24% del share con posición 20+ es evidencia clara de que el trabajo técnico de imágenes falta.

#### 4.1 Consultas de imagen — qué tráfico llega (v7 nuevo)

Análisis del `Consultas.csv` de imagen (antes ignorado en v6):

**Imagen global 16m:** 1,000 consultas visibles / 789 clics / 80,802 impresiones (cap 1,000; Países.csv reporta el total real 1,375c/165k impr).

Clasificando por regla explícita:

| Cluster | Regla | Consultas | Clics | Impresiones |
|---|---|---:|---:|---:|
| Comercial (máquina/molde/vendo + empanada) | contiene `\bmáquinas?\b\|hacer empanada\|molde\|vendo` AND `empanada`, sin marca | 225 | 607 | 27,332 |
| Arepas comercial | contiene `arepa` AND `\bmáquinas?\b` | 44 | 31 | 4,112 |
| Culinario (gourmet/paisa/argentina/venezolana...) | contiene términos culinarios | 74 | 11 | 9,394 |

**Top consultas de imagen (16m):**

| Consulta | Clics | Impresiones | Pos |
|---|---:|---:|---:|
| patacon pisao | 3 | 4,314 | 14.18 |
| **vendo máquina para hacer empanadas** | **59** | **2,820** | **4.93** |
| empanadas argentinas (culinario) | 0 | 2,339 | 82.82 |
| molde para empanadas | 22 | 2,243 | 14.93 |
| **maquina para hacer empanadas** | **81** | **2,096** | **6.25** |
| argentine empanadas (culinario) | 0 | 1,140 | 81.86 |
| empanadas gourmet (culinario) | 1 | 1,130 | 8.11 |
| **precio de maquina de hacer empanadas** | **43** | **1,103** | **3.11** |
| somosa (culinario) | 0 | 1,101 | 27.68 |
| moldes para empanadas | 8 | 1,091 | 9.90 |

**Matiz al hallazgo de v6 "imagen es 24% de oportunidad":**
- El **cluster comercial en imagen rankea entre pos 3 y 6** — está bien posicionado (`vendo máquina` pos 4.93, `precio de máquina` pos 3.11).
- Las **posiciones malas (20-80+) son casi todas consultas culinarias** (`empanadas argentinas` pos 82, `argentine empanadas` pos 81, `empanadas caseras` pos 50). Este tráfico **no es comprador** — es gente buscando fotos de comida.
- La "brecha de posición 20-36" que v6 reportó era un promedio contaminado por tráfico irrelevante, no una oportunidad de producto desatendida.

**Top páginas de imagen (16m):**

| URL | Clics | Impresiones | Pos |
|---|---:|---:|---:|
| `/product/maquina-para-empanadas-cm05s/` | 287 | 20,398 | 11.14 |
| `/` (home ES) | 156 | 14,884 | 34.06 |
| `/product/maquina-para-empanadas-y-arepas-cm06/` | 103 | 9,947 | 23.36 |
| `/product/maquina-para-hacer-empanadas-y-arepas-multifuncional-cm06b/` | 88 | 10,072 | 20.84 |
| `/product/molde-personalizado-intercambiable-para-empanadas-de-trigo/` | 79 | 12,520 | 19.12 |
| `/product/molde-personalizado-fijo-empanada-de-trigo/` | 53 | 10,409 | 19.15 |

**Activo #1 en imagen:** CM05S con 287 clics / 20,398 impr / pos 11 — pieza clave a optimizar para imagen.

**Dispositivos imagen 16m:**

| Dispositivo | Clics | Impresiones | CTR | Pos |
|---|---:|---:|---:|---:|
| Móviles | 1,105 | 99,896 | 1.11% | **17.7** |
| Ordenador | 264 | 64,378 | 0.41% | **41.24** |
| Tablet | 6 | 891 | 0.67% | 28.28 |

**La brecha de posición en imagen es sobre todo desktop** (41.2 vs móvil 17.7). El sitio parece optimizado para móvil pero mal servido en desktop — probable causa: tamaños de imagen no optimizados para thumbnails de Google Images en desktop.

**USA imagen 16m** — el tráfico es 90%+ culinario:

| Consulta | Clics | Impresiones | Pos |
|---|---:|---:|---:|
| empanadas argentinas | 0 | 1,660 | 86.95 |
| patacon pisao | 0 | 1,439 | 13.09 |
| argentine empanadas | 0 | 480 | 89.75 |
| paisas | 0 | 315 | 36.66 |
| gourmet empanadas | 0 | 311 | 16.79 |
| vendo máquina para hacer empanadas | 6 | 224 | 3.77 |
| maquina para hacer empanadas | 8 | 167 | 4.22 |

La pos 36.54 promedio de USA imagen es principalmente **tráfico culinario que no compra**. Cuando USA busca máquina, la posición es 3-4, no 36.

#### 4.2 Consultas de vídeo — confirma que es marginal

Video 16m tiene **7 consultas visibles** en total. La única con volumen es `maquiempanadas` (7 impresiones), el resto son variantes de patacones. Consistente con la incidencia de indexación de video (37 videos con schema mal reconocido).

#### 4.3 YouTube — el activo oculto (v7 nuevo)

Fuente: `.ia/seo/data/youtube/` — canal "Maquina Empanadas Maquiempanadas Colombia" (2011-07 → 2026-09).

**Volúmenes:**
- **3,770,812 views totales** en 15 años
- **1,285,650 views en los últimos 12 meses** (2025-09 → 2026-09)
- **2025 fue un salto masivo:** 1,210,562 views (vs 201,802 en 2024 = **6x**). Algo cambió — video viral, cambio de algoritmo, o estrategia de contenidos nueva. Ver `Datos del gráfico.csv` para identificar el mes exacto.

**Top búsquedas dentro de YouTube (YT_SEARCH — 131K views agregados):**

| Búsqueda en YouTube | Views |
|---|---:|
| maquina para hacer empanadas | **32,358** |
| maquiempanadas (marca) | 31,248 |
| maquina de hacer empanadas | 10,917 |
| maquina de empanadas | 9,104 |
| empanadas colombianas | 4,263 |
| maquina para empanadas | 3,989 |
| maquinas para hacer empanadas | 3,798 |
| máquina para hacer empanadas (tildes) | 3,234 |
| fabrica de empanadas | 2,902 |
| maquina para hacer empanadas automática | 1,796 |
| maquina para hacer arepas | 1,394 |
| empanadas | 1,084 |
| maquina de empanadas automática | 1,001 |

**Las mismas keywords que dominan en Google Search también dominan en YouTube.** La keyword `maquina para hacer empanadas` es la #1 en ambos canales.

**Geografía — top 10 países (2011-2026):**

| País | Views | % |
|---|---:|---:|
| Colombia | 1,528,547 | **48.9%** |
| **Estados Unidos** | **315,968** | **10.1%** |
| España | 165,892 | 5.3% |
| Venezuela | 157,823 | 5.1% |
| México | 121,277 | 3.9% |
| Ecuador | 119,054 | 3.8% |
| Argentina | 101,802 | 3.3% |
| Perú | 69,513 | 2.2% |
| India | 53,109 | 1.7% |
| Chile | 52,352 | 1.7% |

**La distribución geográfica de YouTube es coherente con Google Search:** Colombia ~49% (Google: 37-41%), USA ~10% (Google: 14-17%). Los picos raros (India, Argelia, Bangladesh, Nepal) son ruido de recomendaciones globales, no compradores.

**Implicaciones estratégicas críticas:**

1. **YouTube es un activo enorme desaprovechado en Search.** 3.77M views acumuladas, 1.29M el último año, pero **solo 33 impresiones/16m en Google Search de video**. El markup de video del sitio no reconecta ese activo con búsquedas en Google.
2. **Fase 1 T1 (arreglar video indexing) tiene potencial mucho mayor de lo estimado.** No es solo "desbloquear un tipo de búsqueda que está en 0" — es **reconectar un canal con 1.29M views/año al SEO del sitio**.
3. **La estrategia YouTube-first para las mismas keywords ya está probada:** los videos de Maquiempanadas ya rankean cuando la gente busca "maquina para hacer empanadas" en YouTube. Falta que Google Search los muestre en resultados web.
4. **El salto de 6x en 2025 merece diagnóstico.** Si fue un cambio estratégico intencional, replicarlo; si fue orgánico/viral, entender qué pasó.

### 5. Corrección: share del blog en clics = 20%, no 15%

Recuento sobre Páginas.csv 3m (12 URLs de blog identificables):
- Blog: 961 clics de 4,695 totales = **20.5% de los clics**
- v5 decía "~15%" contando solo 3 URLs. **Corregido.**

Distribución completa (3m, por Páginas.csv):
- Home: 1,807 clics (38.5%)
- Producto/categoría: 1,655 clics (35.3%)
- Blog: 961 clics (20.5%)
- Otras: ~272 clics (5.8%)

**Consecuencia sobre el escenario de "60/40 comercial/informacional" de v5:** era arbitrario y no coincide con los datos reales. La distribución real por URL es ≈75/20/5 (home+producto / blog / otros). Los escenarios cuantitativos en la sección "Escenarios" se recalibran abajo.

### 6. Exports GSC segmentados por país (B3 resuelto)

El usuario obtuvo exports separados para USA y Colombia, 16 meses, por tipo de búsqueda. Datos en `.ia/seo/data/gsc/countries/`. Esto resuelve el bloqueante mayor del análisis.

**Totales USA WEB 16m (Consultas.csv, capped en 1,000 filas):**
- 1,000 consultas visibles / 1,235 clics / 36,538 impresiones (el total real es 2,469c/87,220i según Países.csv — la diferencia es long tail invisible por el cap de GSC)

**Distribución por idioma de las consultas de USA:**

| Idioma inferido | Consultas | Clics | Impresiones | % impr |
|---|---:|---:|---:|---:|
| Español | 758 | 385 | 29,697 | **81.3%** |
| Marca | 20 | 828 | 3,094 | 8.5% |
| Inglés | 24 | 6 | 390 | 1.1% |
| Indeterminado | 198 | 16 | 3,357 | 9.2% |

**Regla de inferencia de idioma (v7 — antes no publicada):**

```python
brand = q ∋ ("maquiempanada" | "maqui empanada" | "maki empanada")
en_signals = ["machine","maker","press","recipe","freeze","how ","can you","can i","best ","buy ","for sale","the ","of ","and ","with ","without","frozen","cooking","cooked"]
es_signals = ["hacer","para","como","cómo","precio","empanadas","empanada","máquina","maquina","molde","arepa","desmech","paisa","colomb","fabric","vend","dulce","salada"]

def lang_of(q):
    if brand(q): return "marca"
    en_hits = |{s ∈ en_signals : s ∈ q}|
    es_hits = |{s ∈ es_signals : s ∈ q}|
    if en_hits > es_hits and en_hits > 0: return "en"
    if es_hits > 0: return "es"
    return "?"
```

Con esta regla exacta, los % de USA son los reportados arriba. Otras reglas razonables pueden dar 71-81% español vs 1-6% inglés — la conclusión direccional (español domina en USA) es robusta a la definición, pero el % puntual depende de la regla.

**Hallazgo mayor:** **USA es un mercado predominantemente hispanohablante** para Maquiempanadas — 71-81% de las impresiones vienen de consultas en español según regla. La versión `/en/` sigue teniendo valor (354 clics, pos 3.58 en USA), pero **no es donde está el volumen**. Reorientar hacia contenido en español para USA es más eficiente que traducir el blog al inglés.

**Top 10 consultas USA (16m):**

| # | Consulta | Clics | Impresiones | Pos |
|---|---|---:|---:|---:|
| 1 | maquina para hacer empanadas | 33 | 2,652 | **5.79** |
| 2 | maquiempanadas | 521 | 1,602 | 2.33 |
| 3 | molde para empanadas | 5 | 1,402 | 8.26 |
| 4 | maquina de hacer empanadas | 33 | 1,013 | 4.96 |
| 5 | maquina para hacer arepas | 21 | 990 | 5.66 |
| 6 | maquina para empanadas | 4 | 743 | 6.81 |
| 7 | maquina de empanadas | 24 | 724 | 2.83 |
| 8 | moldes para empanadas | 0 | 688 | 5.29 |
| 9 | molde de empanadas | 2 | 494 | 7.15 |
| 10 | máquina para hacer empanadas (tildes) | 1 | 480 | 6.58 |

**Comparación USA vs Colombia — término por término (v7 ampliado):**

| Consulta | USA impr | USA pos | USA clics | COL impr | COL pos | COL clics |
|---|---:|---:|---:|---:|---:|---:|
| maquina para hacer empanadas | 2,652 | 5.79 | 33 | 2,560 | **1.95** | 144 |
| molde para empanadas | 1,402 | 8.26 | 5 | 1,755 | **5.03** | 24 |
| maquina para hacer arepas | 990 | 5.66 | 21 | 2,229 | **4.46** | 60 |
| moldes para empanadas | 688 | 5.29 | 0 | 1,343 | **4.00** | 53 |
| maquiempanadas (marca) | 1,602 | **2.33** | 521 | 2,565 | 2.20 | 689 |
| desmechadora de carne | — capped | — | — | 2,445 | 7.57 | 33 |

**Colombia también rankea "15 ideas de negocios que nadie ha explotado" con solo 990 impresiones** (el spike de 4,554 impr en el CSV global viene de otros países). Un dato importante que refuerza que ese blog post es tráfico foráneo, no colombiano.

**Conclusión clave:** las mismas keywords dominan en ambos mercados. La brecha USA vs COL **no es semántica, es de posición**. Colombia rankea pos 2-5, USA rankea pos 5-8 → 3-4x menos clics para el mismo volumen de búsqueda.

**Consultas comerciales en inglés detectadas en USA (16m):**

| Consulta | Impr | Pos |
|---|---:|---:|
| arepa maker machine | 78 | 8.46 |
| empanada machine for sale | 39 | 9.64 |
| the empanada machine | 31 | 9.52 |
| automatic empanada maker machine | 27 | 10.15 |
| empanada machine maker | 15 | 21.33 |
| arepas maker machine | 15 | 8.80 |
| masa machine | 10 | 86.20 |
| arepa machine maker | 9 | 14.33 |
| pupusa machine | 8 | 9.12 |

El mercado anglófono comercial existe pero es marginal (~200-300 impr sumadas). El mercado informacional culinario en inglés (freeze empanadas, how to...) es mayor pero **no compra máquinas**.

**Top páginas de USA WEB 16m:**

| # | URL | Clics | Impresiones | Pos |
|---|---|---:|---:|---:|
| 1 | `/` (home ES) | 482 | 15,603 | 7.16 |
| 2 | `/product/maquina-para-empanadas-cm05s/` | 37 | 4,877 | 5.34 |
| 3 | `/product/...cm06b/` | 53 | 4,838 | 5.48 |
| 4 | `/product/maquina-para-empanadas-y-arepas-cm06/` | 61 | 4,415 | 4.66 |
| 5 | `/videos/` (ES) | 24 | 2,734 | 7.76 |
| 6 | `/en/product-category/moldes/` | 7 | 2,594 | 7.26 |
| 7 | `/en/` (home EN) | 354 | 2,319 | **3.58** |
| 8 | `/en/videos/` | 51 | 2,312 | 2.89 |
| 9 | `/product-category/maquina-de-hacer-empanadas/` | 18 | 2,009 | 5.38 |
| 10 | `/product-category/moldes/` | 2 | 2,001 | 9.12 |

En USA, los productos y home **en español** reciben más clics totales que la versión inglesa. Confirma que el trabajo prioritario es optimizar contenido en español para audiencia latina en USA.

### 7. Google Ads — Search Terms Report + Auction Insights (v7 corregido)

**Fuente:** `.ia/seo/data/google_ads/` — export 2026-01-20 → 2026-09-18 (8 meses).

#### Volumen y economía — dos niveles

El export mezcla dos niveles de agregación que **hay que separar**:

**Nivel A — Total de cuenta (subtotales `Total: Cuenta`, `Total: Máximo rendimiento`, etc.):**

| Métrica | Valor |
|---|---:|
| Costo total cuenta | 19.5M COP (Total: Cuenta) |
| Conv total cuenta (variables entre canales) | 2.5 en Total: Cuenta, pero desglosadas suman diferente |
| Clics totales cuenta | 57,906 |

*Nota: los subtotales `Total: …` **no** son campañas, son agregaciones internas del reporte para tráfico no revelado por término (PMax, Demand Gen, etc.).*

**Nivel B — Términos revelados (sin filas `Total: …`):**

| Métrica | Valor |
|---|---:|
| Filas de términos | 6,008 |
| Términos únicos | **3,424** |
| Clics | 9,227 |
| Impresiones | 53,422 |
| CTR | 17.27% |
| Costo | **9.17M COP ≈ $2,183 USD** |
| Conversiones | 927 |
| CPA | **$9,896 COP ≈ $2.36 USD** |

**Alerta metodológica sobre "conversión":** un CPA de $2.36 USD es **implausible para una venta real de maquinaria industrial**. Casi con seguridad "conversión" en Ads = **lead / formulario / apertura de WhatsApp**, no venta cerrada. Antes de tomar decisiones con este dato, auditar con la cliente qué evento cuenta como conversión y qué % de esos leads termina en máquina vendida (bloqueante B2.1).

#### Campañas etiquetadas (7 reales — todos números verificados fila-por-fila)

| Campaña | Filas revelad. | Términos únicos | Clics | Costo (COP) | Conv | CPA (COP) |
|---|---:|---:|---:|---:|---:|---:|
| [Search] -América (EC, PR, PAN, CL, RD, PE) | 2,372 | 1,982 | 2,427 | 3.6M | 159.5 | 22,626 |
| [P Max] Colombia-PrincipalesCiudades | 1,160 | 1,160 | 2,532 | 2.1M | 351.2 | 6,110 |
| [Search] Colombia Ciudades principales | 1,231 | 995 | 1,468 | 1.4M | 149.7 | 9,368 |
| **[P Max] USA** | **220** | **220** | **1,318** | **1.1M** | **127.7** | **8,966** |
| [P Max] ES | 366 | 366 | 433 | 401K | 52.0 | 7,709 |
| [Search] Marca-Mundo | 580 | 553 | 998 | 319K | 86.6 | 3,683 |
| [Search] - Llamadas | 79 | 74 | 51 | 148K | **0** | ∞ (fuga) |

#### Hallazgos operativos

1. **Fuga confirmada:** `[Search] - Llamadas` gasta 148K COP con **0 conversiones**. Pausar o rehacer.
2. **[P Max] USA existe y funciona** con CPA $8,966 COP (≈$2.13 USD). USA no es hipótesis: es apuesta activa hace 8 meses.
3. **~54.6% de las conversiones reveladas son de marca** (506/927). Sobre el total 7,289 sería 6.9% pero ese denominador **no aplica** (mezcla revelados con Total). Direccionalmente el hallazgo se mantiene: **la marca ya captura eficientemente; la genérica es donde SEO tiene espacio**.
4. **Términos genéricos que convierten** (fuente valiosa para SEO):
   - `maquina para hacer empanadas` — **54 conversiones** en 10 filas (CPA $6,505-14,884 según campaña)
   - `maquina para hacer empanadas colombia` — **20.5 conv** en 11 filas
   - `maquina para hacer empanadas chile` — 7 conv
   - `maquiempanadas miami` — 7 conv
5. **Fugas en inglés (0 conv, >$20K COP c/u):** `food industry machines`, `food production machines`, `boleadora de masa`, `molde para empanadas industrial`, `empamaker peru`. La IA Max/PMax trae tráfico irrelevante — trabajo de exclusiones pendiente.
6. Los términos comerciales en inglés que sí convierten son de baja intención directa a producto (`industrial machinery and equipment` — 5 conv, `industrial machinery` — 3 conv, `ferrero machines` — 1 conv). Puerta de entrada B2B, no compra directa.

#### Auction Insights — Competidores directos

| Dominio | % Impresiones | % Superposición | % Pos superior | % Abs arriba |
|---|---:|---:|---:|---:|
| **Usted (Maquiempanadas)** | **19.65%** | — | — | **41.21%** |
| **ferrero-machines.com** | 12.09% | **33.96%** | 56.10% | **43.32%** |
| adlovermaquinas.com | <10% | 20.48% | 51.50% | 29.96% |
| made-in-china.com | <10% | 14.79% | 21.90% | 17.58% |
| megatiendadeproyectos.com | <10% | 12.19% | 38.83% | 19.00% |
| amazon.com | <10% | 7.30% | 34.42% | 30.93% |

**Lectura:**
- **Ferrero Machines es EL competidor** — aparece con Maquiempanadas en 34% de las subastas, y **le gana en % absoluto arriba de página** (43.32% vs 41.21%).
- Maquiempanadas tiene el mayor share de impresiones pero no la mejor posición.
- **adlovermaquinas.com** es el segundo rival serio (51.5% de posición superior).
- Amazon y made-in-china son ruido de retail/mayoreo.
- Cuando se desbloquee DataForSEO, priorizar `dataforseo_labs/google/competitors_domain/live` sobre `ferrero-machines.com` y `adlovermaquinas.com`.

#### Implicaciones que ajustan el análisis SEO

1. **Si "conversión" ≈ lead y hay ~927 conv de términos revelados en 8 meses**, más el volumen no revelado de PMax/Demand Gen (que llega a 6,300+ conv agregadas en subtotales), el negocio ya tiene alto volumen de leads. El cuello parece **cierre/atención/capacidad**, no adquisición. Confirmar con la cliente (B2.1 y B4).
2. **SEO tiene rol claro: reducir la dependencia de Ads en el cluster genérico**. Hoy `maquina para hacer empanadas` cuesta 6.5-14.9K COP por conv en Ads. Si SEO lo captura orgánicamente, esa plata queda liberada.
3. **Estrategia asimétrica marca vs genérico:**
   - Marca: Ads ya la captura (CPA $3,683 COP). SEO refuerza.
   - Genérica: Ads es caro. **SEO es la palanca de eficiencia**.
4. **Competidor a estudiar antes de gastar en DataForSEO:** `ferrero-machines.com` (auditoría manual gratis).

### 8. Herramientas locales para ejecutar DataForSEO

Ya disponibles en `.ia/seo/`:
- `tools/dataforseo_v3.postman_collection.json` — colección Postman completa (30 MB, 12 módulos)
- `tools/endpoints_priorizados.md` — endpoints seleccionados por pregunta del análisis
- `dataforseo_xmpl_v3_php/` — **SDK oficial en PHP** con `RestClient` y ejemplos por endpoint

Con esto ya no hay excusa técnica para no ejecutar cuando la cuenta esté desbloqueada. Sigue faltando: la verificación de cuenta (40104) y el search terms de Ads.

---

## Sección 8. DataForSEO — validación de mercado (v7 nuevo, ejecutado 2026-09-19)

Fuente: `.ia/seo/data/dataforseo/`. Costo total: **~$0.39 USD** del balance $1. La cuenta se desbloqueó tras verificación por email.

### 8.1 Volumen real de búsqueda (Google Ads volume, no GSC)

Comparación USA vs Colombia para 30 keywords semilla:

| Keyword | USA vol/mes | COL vol/mes | Nota |
|---|---:|---:|---|
| **empanada maker** | **2,400** | 10 | Mercado 100% USA — pero ver 8.2, es tráfico doméstico |
| **empanada press** | **1,600** | 10 | Mercado 100% USA — doméstico |
| laminadora de masa | 720 | **1,300** | Colombia mayor mercado |
| **maquina para hacer empanadas** | **480** | **480** | Volumen idéntico en ambos mercados |
| **empanada machine** | **480** | 10 | 100% USA — comercial industrial (ver 8.2) |
| moldes para empanadas | 480 | 590 | |
| desmechadora de carne | 40 | **480** | Colombia mayor mercado 12x |
| fabrica de empanadas | 70 | 480 | Colombia mayor mercado |
| empanada press machine | 260 | 10 | 100% USA |
| maquina para hacer arepas | 210 | 590 | Colombia mayor |
| arepa maker machine | 170 | 10 | 100% USA |
| arepa machine | 90 | 10 | 100% USA |

**Nota metodológica sobre `language_code`:** el endpoint `keywords_data/google_ads/search_volume` devuelve **el mismo volumen para USA-es y USA-en** para la misma keyword y `location_code`. Google Ads agrupa el volumen por `location_code` sin distinguir idioma real de la búsqueda. Los volúmenes reportados son el mercado total en esa ubicación.

### 8.2 SERP orgánico USA — quién rankea top 10 (validación de intención)

Para cada keyword ganadora de 8.1:

**`empanada maker` (2,400 vol) — el SERP demuestra que NO es comercial industrial:**
- Pos 3: theempanadamaker.com — **es un restaurante en Mission Viejo** (knowledge graph + Yelp reviews)
- Pos 5-6: theempanadamaker.com (más del restaurante)
- Pos 8-11: Amazon, Instagram (prensa doméstica $20-50)
- Features: knowledge_graph (restaurante), popular_products, google_reviews

**`empanada press` (1,600 vol) — dominado por retail doméstico:**
- Pos 5-9: **Amazon, Target, Walmart, Etsy** (prensas de $20-50)
- Pos 6: gratefulgourmetgalena.com
- Ningún fabricante industrial en top 15

**`arepa maker machine` (170 vol) — retail electrodomésticos:**
- Pos 2: Amazon
- Pos 4: eater.com (editorial)
- Pos 7-9: Walmart, **IMUSA** (electrodoméstico $30-100)
- Pos 14: NY Times

**`empanada machine` (480 vol) — SÍ es industrial anglófono:**
- Pos 4: Amazon
- Pos 5-6: **ankofood.com** / anko.com.tw (competidor real, taiwanesa)
- Pos 8: **empanadasmachine.com** (competidor)
- Pos 11: webstaurantstore.com (retail B2B)
- Features: **AI Overview presente**

**`maquina para hacer empanadas` USA español (480 vol) — Maquiempanadas domina:**
- **Pos 2: maquiempanadas.com ✓**
- Pos 7: Amazon
- Pos 9: anko.com.tw
- Pos 11: bascoequipos.cl (competidor chileno)

**Conclusión de 8.2 que corrige la corrección:** los volúmenes "altos" en inglés (`empanada maker` 2,400, `empanada press` 1,600, `arepa maker` 170) son **búsquedas dominadas por consumidor doméstico**, no comprador industrial. El comprador industrial anglófono real es un subconjunto pequeño concentrado en `empanada machine` (480 vol), donde Anko es el rival real.

**El pivote v7 hacia español-primero se sostiene** — el "5x más volumen en inglés" que sugirió 8.1 se disuelve cuando se examina la intención del SERP en 8.2.

### 8.3 Domain rank overview — quién es realmente competitivo en USA

| Dominio | Keywords rankeando | ETV/mes | Pos 1 | Pos 2-3 | Pos 4-10 | Pos 11-20 |
|---|---:|---:|---:|---:|---:|---:|
| **maquiempanadas.com (USA)** | 81 | 298 | **18** | 20 | 14 | 8 |
| maquiempanadas.com (COL) | 55 | 453 | 10 | 10 | 17 | 3 |
| **ankofood.com (USA)** | **145** | **480** | 1 | **43** | 32 | 30 |
| ferrero-machines.com (USA) | 18 | 42 | 1 | 0 | 6 | 10 |

**Hallazgos:**
1. **Maquiempanadas tiene mejor SEO en USA que en Colombia** por número de keywords (81 vs 55) — refuta la percepción de "USA es mercado nuevo".
2. **Anko domina por amplitud** — 43 keywords en pos 2-3. Es el rival orgánico anglófono real, no Ferrero.
3. **Ferrero (el competidor #1 en Ads según Auction Insights) es débil en SEO** — solo 18 keywords, ETV 42. Su fortaleza es la puja pagada, no el orgánico.

### 8.4 Ranked keywords — el patrón de dominio de cada dominio

**Anko top 25 (USA):** su ventaja es **amplitud multi-étnica**, no profundidad en empanadas:
- `pierogi machine` (390 vol, pos 1)
- `egg roll machine` (2,400 vol, pos 2)
- `egg roll maker` (2,400 vol, pos 2)
- `burrito folding machine`, `burrito maker machine` (70 vol c/u, pos 2)
- `kibbeh maker machine`, `paratha maker machine`, `siomai molder`, `spring roll machine`, `croquettes machine`
- **Sus keywords de empanada no aparecen en top 30** — es débil ahí

**Maquiempanadas top 25 (USA):** 18 keywords en **pos 1**, todas en español:
- `molde para empanadas` (480 vol, pos 1)
- `maquina para hacer arepas` (210 vol, pos 1)
- `maquina de hacer empanadas` (210 vol, pos 1)
- `maquiempanadas` (210 vol, pos 1)
- `maquina de empanadas` (140 vol, pos 1)
- `máquina para hacer empanadas colombianas` (30 vol, pos 1)
- **Cero keywords en inglés en top 30**

### 8.5 Estrategia definitiva (basada en 8.1-8.4)

1. **Defender el liderazgo hispano en USA** — 18 pos 1 son un activo defensible con esfuerzo bajo. Mercado no saturado por competencia orgánica.
2. **NO intentar atacar el anglófono industrial vía SEO** — Anko domina 8+ categorías de máquina étnica con recursos de una empresa taiwanesa establecida. Batalla de David vs Goliat sin ventaja natural.
3. **El "mercado anglófono USA" para máquinas de empanadas es más pequeño de lo que sugieren los volúmenes brutos** — el 87% del volumen "empanada maker/press" es consumidor doméstico, no industrial.
4. **Ferrero es el competidor a batir en Ads**, no en SEO. Su débil presencia orgánica sugiere que si Maquiempanadas mejora SEO en las keywords que hoy Ads cubre caras, libera presupuesto.
5. **Consolidar el liderazgo LATAM + expansión España** puede tener más ROI que atacar USA anglófono. España tiene 6.2% del tráfico global con pos promedio 12.58 — mucho margen sin competencia industrial saturada.

### 8.6 Costos DataForSEO consumidos

| Batch | Endpoint | Cost |
|---|---|---:|
| 1 | 3× `search_volume/live` (USA-es, USA-en, COL) | $0.270 |
| 2 | 5× `serp/google/organic/live/advanced` | $0.020 |
| 3 | 4× `domain_rank_overview/live` (Maqui × 2, Anko, Ferrero) | $0.060 |
| 4 | 2× `ranked_keywords/live` (Anko, Maquiempanadas) | $0.031 |
| **Total** | | **~$0.38 USD** |

Balance restante: ~$0.62 USD.

---

## Correcciones respecto a v4 (v5)

| # | Error v4 | Corrección v5 |
|---|---|---|
| 1 | "USA crece más rápido que Colombia" — interpretación **invertida** del ratio | Ratio bajo = aceleración reciente. Colombia (0.52) se aceleró más que USA (0.70). Se **retira** la afirmación y el "34% mayor". |
| 2 | El gráfico histórico tiene **discontinuidad Jul-2025 → Feb-2026**; no es serie continua de 16 meses | Se declara y se limita las comparaciones acumuladas a advertencias. |
| 3 | Reglas de clusters publicadas usaban `maq` como substring — capturaba marca | Reglas reescritas con `\bm[aá]quinas?\b` (word boundary) y exclusión explícita de brand. Cifras recalculadas. |
| 4 | Tabla de idiomas mezclaba agregación por página y por propiedad para producir "~427k impr" | Retirada la resta. Solo se declaran las URLs con rendimiento registrado en cada prefijo. |
| 5 | Techo mezclaba pos/CTR 16m con clics 3m; error aritmético (40% de 716 ≠ 250); benchmarks sin fuente | Recalculado con **solo 3m** consistentemente. Fuentes de CTR citadas. Escenarios explícitos, no "techo". |
| 6 | Sección atribuida a "AI Overviews" | Renombrada a **"Funciones de IA generativa"** (el export incluye AI Overviews + AI Mode sin distinción). |
| 7 | "Prioridad por retorno inmediato" | Reformulada a **"candidatas por visibilidad"** — retorno requiere datos de conversión. |
| 8 | `language` en plan DataForSEO | Corregido a `language_code`. |
| 9 | "Mercado dominado / poca ganancia marginal" | Retirado. GSC mide visibilidad propia, no cuota de mercado. |
| 10 | "Crecimiento sostenido" del cluster (comparando acumulados 3m vs 16m) | Retirado. Se declaran los números crudos sin inferir tendencia. |

---

## Contexto verificado

**Cliente:** Maquiempanadas.

**Catálogo confirmado por URLs indexadas del sitio:**
- Máquinas de empanadas (modelos CM05S, CM06, CM06B multifuncional, CM07, CM08)
- Máquinas de arepas (categoría `/product-category/maquina-de-hacer-arepas/`)
- Moldes (categoría `/product-category/moldes/`)
- Desmechadora (`/product/desmechadora-deshiladora-deshebradora-manual/`)
- Laminadora (`/product/laminadora-harina-de-trigo/`)
- Mezcladora (`/product/mezcladora-con-variador/`)

**Estructura multilenguaje activa (URLs con rendimiento registrado en el export):**

| Prefijo | URLs en export 3m | URLs en export 16m |
|---|---:|---:|
| `/en/` | 138 | 307 |
| `/pt/` | 83 | 140 |
| Total URLs con rendimiento | 435 | 837 |
| Resto (mayoritariamente `/`, español) | ver nota | ver nota |

**Nota:** No se calcula "impresiones del resto" restando /en/ y /pt/ del total del sitio porque ese resto no es una unidad certificada y GSC agrega por página y por propiedad de forma distinta ([ref oficial](https://support.google.com/webmasters/answer/17011364?hl=en)). Estas cifras describen **URLs con rendimiento registrado**, no páginas activas ni indexación certificada.

**Meta comercial (GOAL.md):** 40 máquinas de Maquiempanadas vendidas/mes, sin restricción geográfica declarada.

---

## Datos duros: distribución geográfica del tráfico

### 3 meses (jun-sep 2026)

| País | Clics | Impresiones | CTR | Pos | % impr sitio |
|---|---:|---:|---:|---:|---:|
| Colombia | 2,218 | 68,411 | 3.24% | 5.68 | 40.5% |
| **Estados Unidos** | **716** | **23,348** | **3.07%** | **7.19** | **13.8%** |
| Chile | 174 | 12,227 | 1.42% | 6.76 | 7.2% |
| Argentina | 117 | 11,268 | 1.04% | 8.14 | 6.7% |
| España | 216 | 10,462 | 2.06% | 14.22 | 6.2% |
| México | 95 | 5,920 | 1.60% | 9.01 | 3.5% |
| Venezuela | 101 | 4,470 | 2.26% | 5.91 | 2.6% |

**Total sitio 3m:** 4,534 clics / 168,792 impresiones.

### 16 meses (con discontinuidad — ver siguiente sección)

| País | Clics | Impresiones | CTR | Pos |
|---|---:|---:|---:|---:|
| Colombia | 6,746 | 188,414 | 3.58% | 5.35 |
| Estados Unidos | 2,469 | 87,220 | 2.83% | 7.93 |
| Argentina | 529 | 36,455 | 1.45% | 7.51 |
| España | 842 | 31,968 | 2.63% | 12.58 |
| Chile | 544 | 25,253 | 2.15% | 6.58 |

**Total sitio 16m:** 15,052 clics / 512,176 impresiones.

### Sobre la comparación 3m vs 16m

El `Gráfico.csv` del export 16m muestra **discontinuidad**: hay filas para 17-may-2025 → 21-jun-2025, luego salta a **24-feb-2026 → hoy**. Hay ~8 meses sin datos. La ventana efectiva del "16m" es aproximadamente 8-9 meses, no 16. **No se puede inferir "crecimiento" ni "aceleración"** de estas cifras acumuladas sin serie mensual completa.

Lo único que se puede afirmar honestamente:
- USA es el segundo país por clics e impresiones tanto en 3m como en el acumulado disponible.
- Colombia sigue siendo el primero por un factor 3x en impresiones.

---

## Clusters de keywords — reglas explícitas

**Reglas Python exactas** (evaluadas sobre `Consultas principales.lower()`):

```
brand           = q ∋ ("maquiempanada" | "maqui empanada" | "maki empanada" | "maki-empanada")
maquina_estr    = ¬brand ∧ "empanada" ∈ q ∧ regex(r"\bm[aá]quinas?\b", q)
maquina_ampl    = ¬brand ∧ "empanada" ∈ q ∧ regex(r"\bm[aá]quinas?\b|aparato|selladora|prensadora|prensa de|hacer empanada", q)
arepa           = "arepa" ∈ q
molde           = "molde" ∈ q ∧ "empanada" ∈ q
desmech         = "desmech" ∈ q
lamin           = "lamin" ∈ q
negocio         = q ∋ ("negocio" | "emprendimiento" | "rentab" | "idea" | "emprender" | "franquicia" | "emprendedor")
culinario       = q ∋ ("gourmet" | "congelada" | "típica" | "tipica" | "paisa" | "receta" | "masa" | "rellen" | "precocida" | "freeze" | "how to" | "can you" | "can i" | "ingredient")
transaccional   = "empanada" ∈ q ∧ q ∋ ("precio" | "comprar" | "venta" | "vendo" | "cost" | "for sale" | "buy")
ingles_estricto = q ∋ (∪ señales EN) ∧ ¬(q ∋ (∪ señales ES))
```

Señales EN: `"empanada machine", "empanada maker", "empanada press", "arepa machine", "freeze empanada", "how to", "can you", "can i", "for sale", "recipe"`
Señales ES (excluyentes): `"hacer", "para", "precio", "como", "cómo", "mejor", "empanadas colombia"`

### Resultados (mutuamente **no** excluyentes salvo marca vs máquina)

**3 meses:**

| Cluster | Consultas | Impresiones | Clics | CTR | Pos ponderada |
|---|---:|---:|---:|---:|---:|
| Marca | 14 | 2,693 | 799 | 29.67% | 2.24 |
| Máquina + empanada (estricta) | 137 | 15,047 | 401 | 2.66% | 5.44 |
| Máquina + empanada (ampliada) | 172 | 17,060 | 426 | 2.50% | 5.66 |
| Arepa | 74 | 6,837 | 140 | 2.05% | 6.24 |
| Molde + empanada | 50 | 7,023 | 118 | 1.68% | 5.81 |
| Desmechadora | 21 | 1,899 | 29 | 1.53% | 6.48 |
| Laminadora | 20 | 879 | 7 | 0.80% | 13.52 |
| Negocio/emprendimiento | 145 | 10,398 | 104 | 1.00% | 16.42 |
| Culinario/recetas | 142 | 6,582 | 35 | 0.53% | 10.29 |
| Transaccional + empanada | 27 | 2,041 | 76 | 3.72% | 5.44 |
| Inglés estricto | 26 | 999 | 6 | 0.60% | 10.52 |

**16 meses (con discontinuidad):**

| Cluster | Consultas | Impresiones | Clics | CTR | Pos ponderada |
|---|---:|---:|---:|---:|---:|
| Marca | 21 | 10,475 | 2,691 | 25.69% | 2.20 |
| Máquina + empanada (estricta) | 169 | 57,371 | 1,737 | 3.03% | 5.75 |
| Máquina + empanada (ampliada) | 206 | 63,953 | 1,810 | 2.83% | 5.96 |
| Arepa | 84 | 21,728 | 443 | 2.04% | 6.18 |
| Molde + empanada | 49 | 19,010 | 338 | 1.78% | 6.09 |
| Desmechadora | 28 | 6,383 | 109 | 1.71% | 6.08 |
| Laminadora | 16 | 1,890 | 14 | 0.74% | 13.99 |
| Negocio/emprendimiento | 114 | 23,704 | 238 | 1.00% | 16.58 |
| Culinario/recetas | 127 | 17,555 | 119 | 0.68% | 11.59 |
| Transaccional + empanada | 34 | 7,838 | 350 | 4.47% | 6.54 |
| Inglés estricto | 29 | 2,754 | 20 | 0.73% | 11.41 |

Notas:
- Los CSVs están **limitados a 1,000 filas** (tope de GSC). Long tail invisible.
- Los clusters **no son mutuamente excluyentes** (una consulta puede caer en varios), salvo marca que se excluye de máquina.

---

## Análisis por URL — dónde está posicionado el sitio

### Top 15 páginas por clics (3m)

| # | URL | Clics | Impresiones | CTR | Pos |
|---|---|---:|---:|---:|---:|
| 1 | `/` (home) | 1,572 | 33,961 | 4.63% | 6.01 |
| 2 | `/crear-un-negocio-de-empanadas-una-idea-rentable-y-deliciosa/` | 418 | 22,849 | 1.83% | 5.48 |
| 3 | `/product/maquina-para-empanadas-y-arepas-cm06/` | 225 | 7,450 | 3.02% | 5.27 |
| 4 | `/product-category/moldes/` | 221 | 8,412 | 2.63% | 6.66 |
| 5 | `/en/` (homepage inglés) | 216 | 1,774 | 12.18% | 4.22 |
| 6 | `/presupuesto-para-hacer-100-empanadas/` | 203 | 19,820 | 1.02% | 6.55 |
| 7 | `/product/maquina-para-hacer-empanadas-y-arepas-multifuncional-cm06b/` | 203 | 11,813 | 1.72% | 7.37 |
| 8 | `/product/maquina-para-empanadas-cm05s/` | 136 | 11,434 | 1.19% | 4.56 |
| 9 | `/product-category/maquina-de-hacer-arepas/` | 97 | 3,222 | 3.01% | 5.76 |
| 10 | `/product/desmechadora-...` | 97 | 3,175 | 3.06% | 6.25 |
| 11 | `/crear-una-empresa-de-empanadas/` | 96 | 5,959 | 1.61% | 7.72 |
| 12 | `/en/product/empanadas-and-arepas-machine-cm06/` | 73 | 1,852 | 3.94% | 5.58 |
| 13 | `/product/molde-personalizado-intercambiable-para-empanadas-de-trigo/` | 71 | 2,267 | 3.13% | 4.79 |
| 14 | `/empanadas-congeladas-paso-a-paso-para-lograrlo-con-exito/` | 64 | 8,288 | 0.77% | 8.8 |
| 15 | `/en/product-category/maquina-de-hacer-arepas/` | 62 | 2,065 | 3.00% | 7.73 |

Observaciones:
- La `/en/` homepage muestra CTR 12.18% y pos 4.22 **agregada global** — no está segmentada por país. No se puede afirmar que sea específicamente eficaz en USA sin cruce País × Página.
- Producto CM06 es la landing de producto con mejor eficiencia agregada (pos 5.27, CTR 3%).
- **Blog informacional genera 20.5% de los clics del sitio** (961 de 4,695 en 3m, recuento sobre Páginas.csv). El "15%" de versiones previas se corrige.
- El export por dimensión "Página" **no está cruzado con "País"**. GSC Data Studio/BigQuery permite este cruce; el export CSV normal no.
- El export **Páginas.csv suma 28% más impresiones que Países.csv** para el mismo periodo. Ver "Hallazgos nuevos" arriba.

---

## Funciones de IA generativa

El sitio aparece en funciones de IA generativa de Google Search (**15,253 impresiones/3m**). El export **agrupa AI Overviews + AI Mode sin distinguirlas** ([ref oficial](https://developers.google.com/search/blog/2026/06/gen-ai-performance-reports)).

Distribución mobile: 73% (11,094 de 15,253).

### Top 10 páginas apareciendo en estas funciones

| # | URL | Impresiones (3m) |
|---|---|---:|
| 1 | `/crear-un-negocio-de-empanadas-una-idea-rentable-y-deliciosa/` | 3,545 |
| 2 | `/` (home) | 2,403 |
| 3 | `/presupuesto-para-hacer-100-empanadas/` | 2,181 |
| 4 | `/crear-una-empresa-de-empanadas/` | 861 |
| 5 | `/product/maquina-para-empanadas-y-arepas-cm06/` | 644 |
| 6 | `/product/maquina-para-hacer-empanadas-y-arepas-multifuncional-cm06b/` | 505 |
| 7 | `/15-ideas-de-negocios-rentables-sin-dificultad/` | 470 |
| 8 | `/empanadas-congeladas-paso-a-paso-para-lograrlo-con-exito/` | 397 |
| 9 | `/costos-de-produccion-de-fabrica-de-empanadas/` | 320 |
| 10 | `/como-reducir-costos-de-empanadas/` | 287 |

Distribución por país (3m): USA aporta 1,619 impresiones (10.6%) vs 13.8% del tráfico general. En estas funciones, **USA está sub-representado relativamente** frente al total del sitio.

### Tendencia mensual AI (v7 nuevo)

| Periodo | Impresiones |
|---|---:|
| 2026-06-17 – 06-30 (14 días) | 1,757 |
| 2026-07 (31 días) | 5,122 |
| 2026-08 (31 días) | 5,077 |
| 2026-09 (parcial, 16 días) | 3,297 |

Normalizado por días: jun ≈125/día → jul ≈165/día → ago ≈164/día → sep ≈206/día. **Aparición en IA generativa está creciendo** (~65% de jun a sep). Es la única señal claramente creciente en el análisis; consistente con la expansión general de AI Overviews en Google.

Observación: el contenido informacional del blog es el más presente. Descartarlo (como sugirieron v1/v2) sería un error de visibilidad, aunque no traduzca directamente en ventas.

---

## Escenarios cuantitativos — no "techo"

**Base — USA, últimos 3 meses (todos los temas):**
- 716 clics / 23,348 impresiones / CTR 3.07% / pos promedio 7.19

**Escenario A — proporción comercial ≈75%** (usando distribución real por URL 3m global: home 38.5% + producto/categoría 35.3% = 73.8%):
- 716 × 0.74 ≈ **530 clics comerciales/3m ≈ 177 clics/mes en USA** (asumiendo que la distribución por tipo de URL en USA es similar a la global — supuesto pendiente de verificar con cruce País × Página).

**Escenario B — proyección subiendo a pos 3 (hipotético):**
- CTR promedio pos 3 orgánico ≈ 7-10% desktop, 5-8% móvil ([Advanced Web Ranking Q4-2025](https://www.advancedwebranking.com/ctrstudy/) — CTR real varía por SERP features).
- Multiplicador vs 3.07% actual ≈ 2.3-3.3x.
- Resultado hipotético: **330-470 clics/mes en USA** (todos los temas).

**Conversión a ventas — supuestos, no benchmarks:**
- Click orgánico → contacto WhatsApp/formulario: ¿? — **falta dato del CRM**.
- Contacto → lead calificado: ¿? — **falta dato**.
- Lead → venta de máquina: ¿? — **falta dato**.
- Sin estos tres números, cualquier conversión a "ventas de máquinas" es especulación.

**Lo que sí se puede afirmar:**
- El potencial máximo de clics orgánicos en USA con la posición actual es ~1,000-1,500 clics/mes (asumiendo posición perfecta).
- La conversión de esos clics a máquinas vendidas depende del funnel real, que no está en este análisis.
- SEO es un canal de aporte, no motor único; la meta de 40 máquinas/mes requiere estrategia multicanal.

---

## Candidatas de optimización (por visibilidad, no por retorno demostrado)

Estas son páginas con visibilidad alta y posición mejorable. **No están priorizadas por retorno económico** — requiere data de conversión del CRM para ordenar por revenue esperado.

### Fase 1 — candidatas evidentes (0-90 días)

| URL / arreglo | Justificación por visibilidad | Requisito para priorizar |
|---|---|---|
| **Video indexing** (37 videos con incidencia) | Arreglo técnico barato. Alineado con "universal" de GOAL. | Ninguno — arreglo casi gratis. |
| Landing CM06 (`/product/maquina-para-empanadas-y-arepas-cm06/`) | Pos 5.27, 7.4k impr/3m. Un salto a pos 3 podría duplicar clics. | Tasa de conversión de esta URL. |
| Landing CM06B multifuncional | Pos 7.37, 11.8k impr/3m. Baja posición para volumen alto. | Tasa de conversión. |
| Homepage `/` | Pos 6.01, 34k impr/3m. | Segmentación País × URL. |
| `/en/` y sus productos | CTR 12% agregado. **Pero solo 1.1% de las consultas de USA están en inglés** (dato de export segmentado). Google Ads apunta a `/en/` — pregunta abierta a la cliente: ¿por qué se prioriza `/en/` si el 81% del tráfico orgánico USA es en español? | Confirmar con la cliente. |
| **Contenido en español dirigido a USA** (nueva prioridad) | 81% del tráfico USA es en español. Las mismas keywords que rankean COL en pos 2 rankean USA en pos 5-6. Cerrar esa brecha vale más que traducir. | Verificar hreflang y geo-targeting. |
| Blog informacional | 20% de los clics del sitio. Motor de visibilidad en IA generativa. | Decidir si visibilidad de marca es objetivo o no. |

### Fase 2 — investigación DataForSEO (una vez desbloqueada la cuenta)

Presupuesto acotado ~$5-10 USD.

1. **`keywords_data/google_ads/search_volume/live`** para 50 keywords semilla:
   - `location_code=2840` (USA), `language_code=es` (mercado hispanohablante USA)
   - `location_code=2840`, `language_code=en` (mercado anglófono USA)
   - `location_code=2170` (Colombia) benchmark

2. **`serp/google/organic/live/advanced`** para las 10 ganadoras del paso 1:
   - Competidores en top 3
   - SERP features (imgs, video, shopping, IA generativa)
   - Presencia/ausencia de Maquiempanadas

3. **`serp/google/images/live/advanced`** para top 5 (producto físico → alta relevancia de imágenes en USA).

### Fase 3 — expansión (solo si datos justifican)

- Optimización `/en/` product pages
- Contenido informacional en inglés
- Landing pages específicas USA con schema local

**Contradicción a resolver:** las fases 1 y 3 ambas tocan `/en/`. La fase 1 dice "escalar" y la fase 3 dice "expandir solo si los datos justifican". La coherencia real es: **fase 1 = optimizar lo existente** (contenido presente en `/en/`), **fase 3 = crear contenido nuevo en inglés** (solo si el mercado lo justifica).

---

## Prerrequisitos bloqueantes

1. **[Bloqueante] Verificar cuenta DataForSEO** — status 40104.
2. **[Bloqueante] Acceso al panel de Google Ads** — hay campaña activa (`gad_campaignid=23069071844`). El search terms report es la fuente gratuita más valiosa: keywords **con atribución a conversión**. Debe consultarse antes de gastar en DataForSEO.
3. **[✓ Resuelto — 2026-09-19]** ~~Cruce País × Página × Consulta~~ — obtenidos exports segmentados por país (USA, COL) × tipo de búsqueda (Web/Imagen/Vídeo) × 16m. Ver `.ia/seo/data/gsc/countries/`.
4. **[Bloqueante] Data del CRM**: tasa de conversión de clics orgánicos y de Ads a WhatsApp/formulario, a lead calificado, a venta. Sin esto, todo lo cuantitativo es especulación.
5. **[Cerrado como imposible]** ~~Re-exportar Páginas.csv con filtros verificados~~ — verificado que el delta Páginas-vs-Países es **bidireccional** (global web +28-51%, global imagen +56-59%, pero USA web −11%, CO web −29%, USA imagen −22%). No es problema de filtro mal aplicado; es comportamiento intrínseco de GSC. Conclusión práctica: **no mezclar denominadores entre Páginas y Países**; usar cada dimensión en su propio universo.
6. **[Bloqueante] Confirmar con la cliente**:
   - Envío/soporte real a USA (aunque la existencia de Google Ads a `/en/` sugiere que sí)
   - Alcance del catálogo (arepas, desmechadora, laminadora sí se venden a USA)
   - Cuánto debe aportar SEO al 40/mes en máquinas específicamente
   - Sobre los 37 videos con incidencia de indexación
7. **[Recomendado]** Exports GSC `search type = image` y `= video` para vista universal que pide GOAL.md.
8. **[Recomendado]** Comparación estacional jun-sep 2025 vs jun-sep 2026 (el Gráfico 16m lo permite a nivel sitio) para diferenciar crecimiento de estacionalidad.

---

## Preguntas al segundo LLM (v5)

1. Con la discontinuidad del gráfico histórico documentada, ¿es válido usar el acumulado 16m para algo, o hay que descartarlo y trabajar solo con 3m?
2. Sin cruce País × Página, ¿cómo se valida que `/en/` funcione específicamente en USA y no solo agregado global?
3. Sin data del CRM, ¿tiene sentido presentar escenarios cuantitativos aunque estén marcados como especulativos, o es preferible declarar solo la visibilidad y esperar los datos?
4. ¿La distinción "candidatas por visibilidad" vs "candidatas por retorno" es suficientemente clara para no volver a caer en priorizar sin evidencia comercial?
5. Bajo Revenue Architecture: dado que la evidencia disponible es solo de **adquisición y visibilidad**, ¿es aceptable seguir con la investigación SEO o hay que detenerla hasta obtener datos de retención/expansión/conversión primero?
