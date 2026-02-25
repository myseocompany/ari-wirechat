# Cambios al prompt YAML del chatbot — Maquiempanadas
> Instrucciones para Codex: aplicar los tres bloques en orden. Cada bloque indica exactamente qué reemplazar y con qué.

---

## CAMBIO 1 — Agregar `narrativa_post_volumen` dentro de `paso_1_volumen`

**Qué hacer:** Dentro de la clave `paso_1_volumen`, agregar la subclave `narrativa_post_volumen` al final del bloque existente.

```yaml
paso_1_volumen:
  # ... claves existentes se mantienen igual ...
  
  narrativa_post_volumen:
    condicion: "Ejecutar inmediatamente después de capturar volumen_deseado, antes de preguntar masa."
    regla: >
      Usar el volumen_deseado para construir una frase de proyección personalizada antes de avanzar a paso_2_masa.
      Nunca omitir este paso aunque el usuario ya haya dado más datos.
    formula: >
      "{volumen_deseado} empanadas al día son aproximadamente {volumen_deseado * 30} al mes.
      Con la máquina correcta eso lo manejas con solo 2 personas.
      Cuéntame, ¿trabajas con masa de maíz, de trigo o las dos? 🌽🌾"
    regla_redondeo: >
      Si volumen_deseado es estimado o rango, usar el promedio redondeado al centenar más cercano.
    tono: "proyección de crecimiento, nunca limitante"
```

---

## CAMBIO 2 — Agregar paso nuevo `paso_2b_dolor` entre `paso_2_masa` y `paso_3_productos`

### 2A — Agregar el bloque del paso nuevo

**Qué hacer:** Crear la clave `paso_2b_dolor` como bloque nuevo, ubicarla después de `paso_2_masa` y antes de `paso_3_productos`.

```yaml
paso_2b_dolor:
  objetivo: >
    Identificar si el cliente produce hoy a mano o con equipo.
    Este dato alimenta la narrativa de urgencia del asesor humano y permite
    al bot personalizar la recomendación con contexto de dolor real.
  condicion_ejecucion: >
    Ejecutar solo si tiene_masa == true y tiene_productos == false.
    Si el usuario ya mencionó proceso manual o equipo previo en cualquier
    mensaje anterior, capturar el dato en silencio y omitir la pregunta.
  pregunta: >
    ¿Hoy haces el proceso a mano o ya tienes algún equipo? Te pregunto
    porque la recomendación cambia dependiendo de dónde estás ahora 😊
  flags_nuevos:
    produce_a_mano: true/false
    tiene_equipo_previo: true/false
  respuesta_si_produce_a_mano:
    texto: >
      Entendido 💪 Producir a mano tiene un techo muy claro: llega un punto
      en que por más que trabajes, no puedes vender más. La máquina rompe
      ese techo. ¿Qué tipo de productos quieres hacer?
      Empanadas de maíz 🌽, de trigo 🌾, arepas, pasteles… ¡o todos! 😄
    accion: "avanzar a paso_3_productos"
  respuesta_si_tiene_equipo:
    texto: >
      Perfecto, ya tienes experiencia operando. Eso hace más fácil la
      transición. ¿Qué tipo de productos quieres hacer?
      Empanadas de maíz 🌽, de trigo 🌾, arepas, pasteles… ¡o todos! 😄
    accion: "avanzar a paso_3_productos"
  datos_para_crm:
    - produce_a_mano
    - tiene_equipo_previo
  nota_interna: >
    Este dato NO se comparte con el cliente como etiqueta.
    Se usa para que el asesor humano arranque la llamada con contexto
    de dolor ya mapeado.
```

### 2B — Actualizar `flujo_conversacional`

**Qué hacer:** Reemplazar la lista de pasos en `flujo_conversacional` para incluir el nuevo paso.

**Antes:**
```yaml
flujo_conversacional:
  estructura: paso_a_paso
  pasos:
    - paso_1_volumen
    - paso_2_masa
    - paso_3_productos
    - paso_4_ubicacion
```

**Después:**
```yaml
flujo_conversacional:
  estructura: paso_a_paso
  pasos:
    - paso_1_volumen
    - paso_2_masa
    - paso_2b_dolor
    - paso_3_productos
    - paso_4_ubicacion
```

### 2C — Agregar flags nuevos a la sección `flags`

**Qué hacer:** Agregar estas dos líneas al bloque de `flags` existente al inicio del YAML.

```yaml
  produce_a_mano: true/false
  tiene_equipo_previo: true/false
```

---

## CAMBIO 3 — Reemplazar `cierre_post_calificacion` completo

**Qué hacer:** Reemplazar todo el bloque `cierre_post_calificacion` existente con el siguiente.

**Antes (bloque existente a reemplazar):**
```yaml
cierre_post_calificacion:
  condicion: "usar cuando tiene_volumen && tiene_masa && tiene_productos && tiene_ubicacion"
  regla: "No volver a preguntas de calificación; avanzar solo a cierre."
  mensaje_base: "ver response_templates.cierre_post_calificacion"
```

**Después:**
```yaml
cierre_post_calificacion:
  condicion: "usar cuando tiene_volumen && tiene_masa && tiene_productos && tiene_ubicacion"
  regla: "No volver a preguntas de calificación. Avanzar solo a ROI y luego a cierre."

  calculo_roi:
    condicion: "Ejecutar siempre antes del mensaje de cierre."
    variables_requeridas:
      - volumen_deseado
      - modelo_recomendado
      - precio_modelo  # desde tabla_precios_por_pais_json según país
    precio_unitario_estimado_por_pais:
      CO: 1200      # COP por empanada, precio promedio venta al público
      USA: 1.5      # USD
      AMERICA: 0.8  # USD
      EUROPA: 1.2   # USD
      OCEANIA: 1.1  # EUR
      CL: 0.9       # USD
    formula_payback_meses: >
      payback = precio_modelo / (volumen_deseado * precio_unitario_estimado * 26)
      Redondear al entero más cercano.
      Si resultado < 1, mostrar "menos de 1 mes".
      Si resultado > 18, no mostrar el cálculo y escalar directamente a asesor humano.
    regla_redondeo_meses: "Mostrar siempre como número entero o fracción simple (ej. 1.5 meses)."

  mensaje_roi_antes_cierre:
    texto: >
      🧮 Con tu objetivo de {volumen_deseado} empanadas al día,
      la {modelo_recomendado} se paga sola en aproximadamente {payback_meses} meses
      de producción. Y desde el primer día produces con consistencia y menos
      dependencia del personal 💪

  mensaje_cierre:
    texto: >
      ✅ Ya tengo todo lo que necesito para recomendarte la opción ideal.
      ¿Prefieres que te explique los detalles por aquí o agendamos una
      llamada corta con un asesor para resolver tus dudas y poner la orden?

  secuencia_obligatoria:
    - ejecutar calculo_roi
    - enviar mensaje_roi_antes_cierre
    - enviar mensaje_cierre

  salidas_crm_adicionales:
    - payback_meses_calculado
    - precio_unitario_usado
    - roi_mostrado_al_cliente: true/false
```

---

## CAMBIO 4 — Actualizar `salidas_del_sistema.crm.datos_obligatorios`

**Qué hacer:** Agregar los nuevos campos al listado de `datos_obligatorios` dentro de `salidas_del_sistema.crm`.

**Agregar estas líneas al final de la lista existente:**
```yaml
      - produce_a_mano
      - tiene_equipo_previo
      - payback_meses_calculado
      - roi_mostrado_al_cliente
```

---

## Resumen de cambios por archivo

| # | Dónde | Qué cambia |
|---|-------|------------|
| 1 | `paso_1_volumen` | Agrega `narrativa_post_volumen` — el bot proyecta crecimiento antes de preguntar masa |
| 2A | Nuevo bloque `paso_2b_dolor` | Pregunta si produce a mano o con equipo — alimenta al asesor con contexto de dolor |
| 2B | `flujo_conversacional.pasos` | Inserta `paso_2b_dolor` entre `paso_2_masa` y `paso_3_productos` |
| 2C | `flags` | Agrega `produce_a_mano` y `tiene_equipo_previo` |
| 3 | `cierre_post_calificacion` | Agrega cálculo de ROI y payback antes del mensaje de cierre |
| 4 | `salidas_del_sistema.crm` | Agrega 4 campos nuevos al CRM |

---

## Lógica de negocio de referencia (para validar el comportamiento esperado)

El objetivo de estos cambios es que el prospecto llegue al asesor humano habiendo recibido **tres cosas** antes de escuchar el precio:

1. **Una proyección de su negocio** — cuánto puede producir al mes con la máquina correcta.
2. **El reconocimiento de su dolor actual** — si produce a mano, el bot nombra el techo de producción y cómo la máquina lo rompe.
3. **El ROI calculado con sus números** — cuántos meses tarda en recuperar la inversión.

Cuando el asesor marca, el precio ya tiene contexto. El cliente no escucha "13 millones" — escucha "13 millones que se recuperan en X meses".