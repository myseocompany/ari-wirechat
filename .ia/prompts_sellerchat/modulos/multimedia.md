modulo:
  nombre: multimedia
  requiere_core: true
  uso: multimedia

multimedia_maquinas:
  regla_general: >
    Solo usar modelos presentes en machine_models_json. Si el modelo no existe, pedir aclaración.
  CM05S:
    fotos:
      - /m/2021-08/cm05s.jpg
    video: /maquina-para-hacer-empanadas-semiautomatica-para-una-persona/
  CM06:
    fotos:
      - /m/2025-02/cm06.webp
    video: /maquina-para-hacer-patacones-y-tostones/
  CM06B:
    fotos:
      - /m/2025-02/CM06B.webp
    video: /maquina-para-hacer-arepas-de-huevo/
  CM07:
    fotos:
      - /m/2025-02/CM07.webp
    video: /maquina-para-hacer-pasteles/
  CM08:
    fotos:
      - /m/2025-02/CM08_1.webp
    video: /maquina-para-hacer-empanadas-semiautomatica-para-una-persona/

multimedia_productos:
  pelapapas:
    video: /maquina-para-hacer-empanadas-semiautomatica-para-dos-personas/
  laminadora_trigo:
    url: /product/laminadora-harina-de-trigo/
    video: /maquina-para-hacer-empanadas-cocteleras/
  laminadora_variador:
    url: /product/laminadora-fondan-pizza-trigo/
    video: /maquina-para-hacer-empanadas-cocteleras/

comportamiento_multimedia:
  trigger_keywords:
    - foto
    - imag
    - video
    - maquina
  reglas:
    - Si menciona raíz pelapap, enviar /maquina-para-hacer-empanadas-semiautomatica-para-dos-personas/.
    - Si menciona raíz laminador, enviar /maquina-para-hacer-empanadas-cocteleras/.
  respuesta: "ver response_templates.multimedia_modelo"
