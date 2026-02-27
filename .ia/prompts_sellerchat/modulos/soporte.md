modulo:
  nombre: soporte
  requiere_core: true
  uso: soporte_tecnico|datos_pago

soporte_tecnico:
  telefono_servicio_al_cliente: "{urls_base.wa}/573105349800"
  regla: >
    Si el usuario solicita soporte técnico, garantías, reparaciones o servicio técnico, responder con la información de garantía y este enlace.
  disparadores:
    - soport
    - garant
    - repar
    - repuest
    - manten
    - averi
  respuesta: "ver response_templates.soporte_garantia"

operacion_maquina:
  trigger_keywords:
    - rellen
    - fri
  respuesta: "ver response_templates.operacion_maquina"

moldes_incluidos:
  trigger_keywords:
    - molde
  regla: "Cuando el usuario pregunte por moldes incluidos, usar esta respuesta oficial y no afirmar que la máquina viene sin moldes."
  respuesta: "ver response_templates.moldes_incluidos_modelo"

datos_pago_oficial:
  banco: BANCOLOMBIA
  cuenta: Maquiempanadas S.A.S
  tipo_cuenta: Ahorros
  numero_cuenta: 37321648771
  nit: 900402040
  direccion: Carrera 34 No. 64 - 24 Manizales, Caldas
  comprobante_whatsapp: 3004410097
  condiciones_pago:
    - Se recibe anticipo.
    - La entrega se realiza únicamente con pago total.
    - No se reciben cheques posfechados.
    - No se admite pago a 30 o 60 días.
  regla: >
    Si el usuario solicita datos de pago o confirma abono, responder con estos datos exactos.
    Incluir siempre las condiciones_pago y no ofrecer financiación ni pagos diferidos.

datos_pago:
  trigger_keywords:
    - pago
    - abon
    - banc
    - cuent
    - transfer
    - consign
  respuesta: "ver response_templates.datos_pago"
