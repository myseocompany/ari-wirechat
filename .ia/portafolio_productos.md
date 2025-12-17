# Guía de portafolio de máquinas

Este documento resume la información disponible en `.ia/products.ts` para que un LLM entienda el portafolio actual: regiones de venta, capacidades de producto y precios por modelo.

## Regiones y parámetros comerciales

| Código | Nombre | Moneda (símbolo) | Salario horario sugerido | Región de precios | Prefijo telefónico |
| --- | --- | --- | --- | --- | --- |
| CO | Colombia | COP ($) | 10895 | CO | +57 |
| CL | Chile | USD ($) | 3.1 | CL | +56 |
| AMERICA | América | USD ($) | 2.5 | AMERICA | +52 |
| USA | Estados Unidos | USD ($) | 15 | USA | +1 |
| EUROPA | Europa | USD ($) | 10 | EUROPA | +34 |
| OCEANIA | Oceanía | USD ($) | 16 | OCEANIA | +61 |

## Capacidades de producto

- `empanadas-trigo`: Empanadas con masa de trigo.
- `empanadas-maiz`: Empanadas con masa de maíz.
- `arepas`: Arepas tradicionales.
- `arepas-rellenas`: Arepas rellenas tipo arepa-burger.
- `patacones`: Patacones prensados.
- `aborrajados`: Aborrajados de plátano.
- `pasteles`: Pasteles fritos.

## Modelos disponibles y precios

Cada modelo incluye la producción horaria estimada y los precios listados por región.

### CM05S

- Producción: 1600 empanadas/hora.
- Capacidades: empanadas-trigo, empanadas-maiz, arepas, arepas-rellenas, aborrajados, pasteles.
- Precios:
  | Región | Moneda | Precio |
  | --- | --- | --- |
  | CO | COP | 34886280 |
  | CL | USD | 10285 |
  | AMERICA | USD | 9885 |
  | USA | USD | 10873 |
  | EUROPA | USD | 10285 |
  | OCEANIA | USD | 9256 |

### CM06

- Producción: 500 empanadas/hora.
- Capacidades: empanadas-maiz, arepas.
- Precios:
  | Región | Moneda | Precio |
  | --- | --- | --- |
  | CO | COP | 13026822 |
  | CL | USD | 4383 |
  | AMERICA | USD | 4133 |
  | USA | USD | 4546 |
  | EUROPA | USD | 4249 |
  | OCEANIA | USD | 3824 |

### CM06B

- Producción: 500 empanadas/hora.
- Capacidades: empanadas-maiz, arepas, arepas-rellenas, patacones, aborrajados, pasteles.
- Precios:
  | Región | Moneda | Precio |
  | --- | --- | --- |
  | CO | COP | 17892000 |
  | CL | USD | 5684 |
  | AMERICA | USD | 5434 |
  | USA | USD | 5977 |
  | EUROPA | USD | 5550 |
  | OCEANIA | USD | 4995 |

### CM07

- Producción: 400 empanadas/hora.
- Capacidades: empanadas-trigo.
- Precios:
  | Región | Moneda | Precio |
  | --- | --- | --- |
  | CO | COP | 15450000 |
  | CL | USD | 5031 |
  | AMERICA | USD | 4781 |
  | USA | USD | 5259 |
  | EUROPA | USD | 4897 |
  | OCEANIA | USD | 4407 |

### CM08

- Producción: 500 empanadas/hora.
- Capacidades: empanadas-trigo, empanadas-maiz, arepas, arepas-rellenas, patacones, aborrajados, pasteles.
- Precios:
  | Región | Moneda | Precio |
  | --- | --- | --- |
  | CO | COP | 19252296 |
  | CL | USD | 6048 |
  | AMERICA | USD | 5798 |
  | USA | USD | 6377 |
  | EUROPA | USD | 5914 |
  | OCEANIA | USD | 5322 |

## Notas operativas

- Los precios provienen directamente de la estructura `MACHINE_MODELS` y ya incorporan la moneda definida para cada región.
- El salario horario sugerido permite estimar costos laborales mensuales considerando 20 días productivos al mes (`DAYS_PER_MONTH`), si se requiere.
