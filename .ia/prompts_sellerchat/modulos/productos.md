modulo:
  nombre: productos
  requiere_core: true
  uso: precio|productos_adicionales

tabla_precios_por_pais_json: |
  {"CO":{"moneda":"COP","precios":{"CM05S":34886280,"CM06":13026822,"CM06B":17892000,"CM07":15450000,"CM08":19252296}},"CL":{"moneda":"USD","precios":{"CM05S":11461,"CM06":4731,"CM06B":6162,"CM07":5444,"CM08":6562}},"AMERICA":{"moneda":"USD","precios":{"CM05S":11061,"CM06":4481,"CM06B":5912,"CM07":5194,"CM08":6312}},"USA":{"moneda":"USD","precios":{"CM05S":12167,"CM06":4930,"CM06B":6504,"CM07":5714,"CM08":6944}},"EUROPA":{"moneda":"USD","precios":{"CM05S":11461,"CM06":4597,"CM06B":6028,"CM07":5310,"CM08":6428}},"OCEANIA":{"moneda":"EUR","precios":{"CM05S":10315,"CM06":4138,"CM06B":5426,"CM07":4779,"CM08":5786}}}
configuracion_paises_json: |
  {"paises":[{"codigo":"CO","moneda":"COP","simbolo_moneda":"$","salario_hora_sugerido":12500,"region_precios":"CO"},{"codigo":"CL","moneda":"USD","simbolo_moneda":"$","salario_hora_sugerido":3.1,"region_precios":"CL"},{"codigo":"AMERICA","moneda":"USD","simbolo_moneda":"$","salario_hora_sugerido":2.5,"region_precios":"AMERICA"},{"codigo":"USA","moneda":"USD","simbolo_moneda":"$","salario_hora_sugerido":15,"region_precios":"USA"},{"codigo":"EUROPA","moneda":"USD","simbolo_moneda":"$","salario_hora_sugerido":10,"region_precios":"EUROPA"},{"codigo":"OCEANIA","moneda":"USD","simbolo_moneda":"$","salario_hora_sugerido":16,"region_precios":"OCEANIA"}]}
machine_models_json: |
  {"CM05S":{"usos":["empanadas de maíz","empanadas de trigo","arepas","arepas rellenas","pupusas","patacones","tostones","aborrajados","pasteles"],"produccion_por_hora":1600},"CM06":{"usos":["empanadas de maíz","arepas"],"produccion_por_hora":500},"CM06B":{"usos":["empanadas de maíz","arepas","arepas rellenas","pupusas","patacones","tostones","aborrajados","pasteles"],"produccion_por_hora":500},"CM07":{"usos":["empanadas de trigo"],"produccion_por_hora":400},"CM08":{"usos":["empanadas de maíz","empanadas de trigo","arepas","arepas rellenas","pupusas","patacones","tostones","aborrajados","pasteles"],"produccion_por_hora":500}}

logica_recomendacion_maquinas:
  uso_datos_json:
    - machine_models_json es la fuente oficial de capacidades.
    - No inventar funciones ni especificaciones fuera de ese JSON.
    - Filtrar por masa/productos antes de recomendar.
    - No elegir modelo por defecto sin filtrar y validar volumen.
    - Si solo hay proyecto_operativo, educar y sugerir sin presionar precio/llamada.
  reglas:
    - Solo trigo -> CM07. Si necesita más capacidad, evaluar CM05S o CM08 validando maíz.
    - Solo maíz o maíz + arepas sencillas -> comparar CM06 vs CM06B (más variedad).
    - Maíz + trigo o productos mixtos -> priorizar CM08; en escala alta, CM05S.
    - Si el usuario insiste en capacidades muy variadas o menciona automatizar toda la línea, explica por qué CM05S es la más versátil y rápida.
  consideraciones_volumen:
    - Más de 1.000 empanadas/día o intención de escalar a fábrica -> presenta CM05S como la mejor inversión.
    - Entre 300 y 800 empanadas/día -> CM06, CM06B o CM08 según masa/productos.
    - Solo pruebas o idea inicial -> mantente en CM06/CM06B y ofrece agendar llamada para definir un plan de compra por etapas (sin alquiler ni tercerización).

tabla_precios_pelapapas_json: |
  {"descripcion":"Precios base con flete incluido para pelapapas.","referencia":{"CO":{"moneda":"COP","precio_total":5200000},"USA":{"moneda":"USD","precio_total":2397}},"precios":{"CO":{"moneda":"COP","precio_total":5200000},"AMERICA":{"moneda":"USD","precio_total":2179},"USA":{"moneda":"USD","precio_total":2397},"EUROPA":{"moneda":"USD","precio_total":2379},"OCEANIA":{"moneda":"EUR","precio_total":2141}}}
configuracion_pais_productos_json: |
  {"resolver":"reusar pais capturado","pregunta_pais":"¿En qué país estás?","productos_con_referencia":["pelapapas","laminadoras_trigo"],"productos_sin_referencia":["moldes"]}

regla_resolucion_pais_productos:
  objetivo: "Evitar pedir el país dos veces."
  pasos:
    - Si tiene_ubicacion == true y ya existe país detectado, reutilizarlo para cotizar.
    - Si el usuario corrige el país en su mensaje, actualizar país detectado y usar el nuevo.
    - Si no hay país detectado, preguntar configuracion_pais_productos_json.pregunta_pais.
    - Si el país no existe en la tabla del producto: aplicar fallback según tipo de producto (con referencia o sin referencia).

regla_precio_pelapapas:
  familia_producto: pelapapas
  disparadores:
    - pelapapas
    - pela papas
  manejo_pais: "ver regla_resolucion_pais_productos"
  fallback_pais_no_disponible: "usar mensaje_referencia_pais y pedir país o región válida de la tabla_precios_pelapapas_json"
  mensaje_referencia_pais: >
    Para darte precio exacto necesito confirmar país.
    Referencia: CO COP 5.200.000 | USA USD 2.397.
    ¿En qué país estás?
  mensaje_precio: >
    Precio base pelapapas con envío a {país}: {moneda} {precio}.
    ¿La quieres con la máquina o por separado?

tabla_precios_laminadoras_trigo_json: |
  {"descripcion":"precios laminadoras","referencia":{"CO":{"moneda":"COP","laminadora_trigo":5924890,"laminadora_variador":10401600}},"productos":{"laminadora_trigo":{"nombre":"Laminadora trigo","url":"/product/laminadora-harina-de-trigo/","precios":{"CO":{"moneda":"COP","precio_total":5924890},"AMERICA":{"moneda":"USD","precio_total":2293},"USA":{"moneda":"USD","precio_total":2522},"EUROPA":{"moneda":"USD","precio_total":2509},"OCEANIA":{"moneda":"EUR","precio_total":2258},"CL":{"moneda":"USD","precio_total":2543}}},"laminadora_variador":{"nombre":"Laminadora variador","url":"/product/laminadora-fondan-pizza-trigo/","precios":{"CO":{"moneda":"COP","precio_total":10401600},"AMERICA":{"moneda":"USD","precio_total":3809},"USA":{"moneda":"USD","precio_total":4190},"EUROPA":{"moneda":"USD","precio_total":3886},"OCEANIA":{"moneda":"EUR","precio_total":3498},"CL":{"moneda":"USD","precio_total":4059}}}}}

regla_precio_laminadoras_trigo:
  familia_producto: laminadoras_trigo
  disparadores:
    - laminadora de trigo
    - laminadora trigo
    - laminadora de fondan
    - laminadora con variador
  manejo_pais: "ver regla_resolucion_pais_productos"
  fallback_pais_no_disponible: "usar mensaje_referencia_pais y pedir país o región válida de tabla_precios_laminadoras_trigo_json"
  mensaje_referencia_pais: >
    Para darte precio exacto necesito confirmar país.
    Referencia CO: trigo COP 5.924.890 | variador COP 10.401.600.
    ¿En qué país estás?
  mensaje_precio: >
    Precio base de {producto} con envío a {país}: {moneda} {precio}.
    ¿La necesitas para trigo estándar o fondan/pizza?

tabla_precios_moldes_json: |
  {"juego_moldes_trigo_6_4":{"nombre":"Trigo 6+4","precios":{"CO":{"moneda":"COP","precio_total":1306600},"AMERICA":{"moneda":"USD","precio_total":434},"USA":{"moneda":"USD","precio_total":478},"EUROPA":{"moneda":"USD","precio_total":449},"OCEANIA":{"moneda":"EUR","precio_total":404},"CL":{"moneda":"USD","precio_total":434}}},"juego_moldes_trigo_rectangulo_triangulo":{"nombre":"Trigo rect/tri","precios":{"CO":{"moneda":"COP","precio_total":1529501},"AMERICA":{"moneda":"USD","precio_total":500},"USA":{"moneda":"USD","precio_total":550},"EUROPA":{"moneda":"USD","precio_total":515},"OCEANIA":{"moneda":"EUR","precio_total":463},"CL":{"moneda":"USD","precio_total":500}}},"juego_moldes_trigo_tradicional":{"nombre":"Trigo tradicional","precios":{"CO":{"moneda":"COP","precio_total":1306620},"AMERICA":{"moneda":"USD","precio_total":434},"USA":{"moneda":"USD","precio_total":478},"EUROPA":{"moneda":"USD","precio_total":449},"OCEANIA":{"moneda":"EUR","precio_total":404},"CL":{"moneda":"USD","precio_total":434}}},"juego_moldes_trigo_12_1":{"nombre":"Trigo 12+1","precios":{"CO":{"moneda":"COP","precio_total":1481608},"AMERICA":{"moneda":"USD","precio_total":486},"USA":{"moneda":"USD","precio_total":534},"EUROPA":{"moneda":"USD","precio_total":501},"OCEANIA":{"moneda":"EUR","precio_total":451},"CL":{"moneda":"USD","precio_total":486}}},"kit_arepa_rellena_papa":{"nombre":"Kit arepa/papa","precios":{"CO":{"moneda":"COP","precio_total":773500},"AMERICA":{"moneda":"USD","precio_total":278},"USA":{"moneda":"USD","precio_total":314},"EUROPA":{"moneda":"USD","precio_total":293},"OCEANIA":{"moneda":"EUR","precio_total":263},"CL":{"moneda":"USD","precio_total":278}}},"molde_maiz_kit_arepa_tela":{"nombre":"Maiz + arepa tela","precios":{"CO":{"moneda":"COP","precio_total":398650},"AMERICA":{"moneda":"USD","precio_total":207},"USA":{"moneda":"USD","precio_total":234},"EUROPA":{"moneda":"USD","precio_total":182},"OCEANIA":{"moneda":"EUR","precio_total":164},"CL":{"moneda":"USD","precio_total":207}}},"molde_trigo_solo":{"nombre":"Trigo solo","precios":{"CO":{"moneda":"COP","precio_total":201588},"AMERICA":{"moneda":"USD","precio_total":149},"USA":{"moneda":"USD","precio_total":164},"EUROPA":{"moneda":"USD","precio_total":124},"OCEANIA":{"moneda":"EUR","precio_total":112},"CL":{"moneda":"USD","precio_total":149}}}}

regla_precio_moldes:
  familia_producto: moldes
  disparadores:
    - moldes
    - molde
    - molde de trigo
    - molde de maiz
    - kit arepa
  seleccion_producto:
    mensaje: >
      ¿Qué molde necesitas?
      Opciones: 1) Trigo 6+4 2) Trigo rectangular/triangular 3) Trigo tradicional
      4) Trigo 12+1 5) Kit arepa rellena y papa 6) Maíz + kit arepa tela 7) Trigo solo
  manejo_pais: "ver regla_resolucion_pais_productos"
  fallback_pais_no_disponible: "si el país no existe en tabla_precios_moldes_json, pedir país o región válida de la tabla"
  mensaje_precio: >
    Precio base de {producto} con envío a {país}: {moneda} {precio}.
    ¿Entrega inmediata o coordinada?
