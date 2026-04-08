const pptxgen = require("pptxgenjs");

// Color palette
const C = {
  navy: "1B2B4B",
  amber: "C8842A",
  lightBg: "F7F4EF",
  white: "FFFFFF",
  muted: "6B7B8D",
  lightAccent: "E8D5B5",
  red: "B03A2E",
  green: "2E7D32",
  darkNavy2: "243654",
};

const makeShadow = () => ({
  type: "outer",
  blur: 6,
  offset: 2,
  angle: 135,
  color: "000000",
  opacity: 0.12,
});

let pres = new pptxgen();
pres.layout = "LAYOUT_16x9";
pres.author = "Consultoría Comercial";
pres.title = "Método de Ventas B2B - Maquiempanadas";

// ============================================================
// SLIDE 1 — PORTADA
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Top-right amber accent rectangle
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 8.5, y: 0, w: 1.5, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Main title
  slide.addText("Método de Ventas B2B", {
    x: 0.6, y: 1.8, w: 7.6, h: 1.1,
    fontFace: "Georgia",
    fontSize: 44,
    bold: true,
    color: C.white,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText("Metodología SPIN · Maquiempanadas", {
    x: 0.6, y: 3.0, w: 7.6, h: 0.6,
    fontFace: "Calibri",
    fontSize: 22,
    color: C.amber,
    align: "left",
    margin: 0,
  });

  // Below subtitle
  slide.addText("Diseñado para el equipo comercial · 2026", {
    x: 0.6, y: 3.7, w: 7.6, h: 0.45,
    fontFace: "Calibri",
    fontSize: 13,
    color: C.white,
    align: "left",
    margin: 0,
  });

  // Bottom left confidential
  slide.addText("Confidencial — Entregable de consultoría", {
    x: 0.6, y: 5.1, w: 7.0, h: 0.35,
    fontFace: "Calibri",
    fontSize: 10,
    color: C.muted,
    align: "left",
    margin: 0,
  });
}

// ============================================================
// SLIDE 2 — EL PROBLEMA
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("¿Por qué la venta tradicional no funciona aquí?", {
    x: 0.25, y: 0.2, w: 9.5, h: 0.65,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  // Left column header
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.25, y: 1.0, w: 4.5, h: 0.45,
    fill: { color: C.red },
    line: { color: C.red },
  });
  slide.addText("Lo que hacen hoy", {
    x: 0.25, y: 1.0, w: 4.5, h: 0.45,
    fontFace: "Calibri",
    fontSize: 13,
    bold: true,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  const leftItems = [
    "Hablan de características antes de entender el negocio",
    "Dan el precio sin haber encontrado el problema",
    "Convencen en lugar de preguntar",
  ];

  leftItems.forEach((item, i) => {
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 0.25, y: 1.5 + i * 0.7, w: 4.5, h: 0.62,
      fill: { color: C.white },
      line: { color: "DDDDDD", width: 0.5 },
      shadow: makeShadow(),
    });
    slide.addText([{ text: item, options: { bullet: true } }], {
      x: 0.3, y: 1.5 + i * 0.7, w: 4.4, h: 0.62,
      fontFace: "Calibri",
      fontSize: 12,
      color: "333333",
      valign: "middle",
      margin: 5,
    });
  });

  // Right column header
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 5.0, y: 1.0, w: 4.75, h: 0.45,
    fill: { color: C.green },
    line: { color: C.green },
  });
  slide.addText("Lo que logra el método SPIN", {
    x: 5.0, y: 1.0, w: 4.75, h: 0.45,
    fontFace: "Calibri",
    fontSize: 13,
    bold: true,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  const rightItems = [
    "El cliente descubre el problema por sí solo",
    "El precio aparece después del valor, no antes",
    "Las objeciones desaparecen antes de aparecer",
  ];

  rightItems.forEach((item, i) => {
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 5.0, y: 1.5 + i * 0.7, w: 4.75, h: 0.62,
      fill: { color: C.white },
      line: { color: "DDDDDD", width: 0.5 },
      shadow: makeShadow(),
    });
    slide.addText([{ text: item, options: { bullet: true } }], {
      x: 5.05, y: 1.5 + i * 0.7, w: 4.65, h: 0.62,
      fontFace: "Calibri",
      fontSize: 12,
      color: "333333",
      valign: "middle",
      margin: 5,
    });
  });

  // Bottom quote box
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.25, y: 4.65, w: 9.5, h: 0.75,
    fill: { color: C.lightAccent },
    line: { color: C.lightAccent },
  });
  slide.addText(
    "Tu trabajo no es convencer. Es entender. Si el problema es real y la solución encaja, la venta fluye sola.",
    {
      x: 0.3, y: 4.65, w: 9.4, h: 0.75,
      fontFace: "Calibri",
      fontSize: 12,
      italic: true,
      color: C.navy,
      align: "center",
      valign: "middle",
      margin: 5,
    }
  );
}

// ============================================================
// SLIDE 3 — LOS 4 TIPOS DE PREGUNTAS
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("La lógica del método: 4 preguntas en orden", {
    x: 0.25, y: 0.18, w: 9.5, h: 0.55,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText("Cada tipo de pregunta tiene un propósito. El orden importa.", {
    x: 0.25, y: 0.78, w: 9.5, h: 0.35,
    fontFace: "Calibri",
    fontSize: 13,
    color: C.muted,
    align: "left",
    margin: 0,
  });

  const cards = [
    { letter: "S", circleColor: C.navy, title: "SITUACIÓN", desc: "Hechos del negocio de hoy. Pocos y precisos — el cliente se aburre con demasiadas.", borderColor: C.navy },
    { letter: "P", circleColor: C.amber, title: "PROBLEMA", desc: "¿Qué los puso a buscar la máquina? Calla. Escucha sin interrumpir ni sugerir.", borderColor: C.amber },
    { letter: "I", circleColor: C.red, title: "IMPLICACIÓN", desc: "Cuánto les cuesta ese problema en plata, clientes y crecimiento. La parte más poderosa — y la más saltada.", borderColor: C.red },
    { letter: "N", circleColor: C.green, title: "NECESIDAD-BENEFICIO", desc: "El cliente describe cómo sería su negocio sin el problema. Que él lo diga — no tú.", borderColor: C.green },
  ];

  cards.forEach((card, i) => {
    const yPos = 1.2 + i * 1.0;
    const cardH = 0.88;

    // Card background
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 0.25, y: yPos, w: 9.5, h: cardH,
      fill: { color: C.white },
      line: { color: "EEEEEE", width: 0.5 },
      shadow: makeShadow(),
    });

    // Left colored border
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 0.25, y: yPos, w: 0.08, h: cardH,
      fill: { color: card.borderColor },
      line: { color: card.borderColor },
    });

    // Circle with letter
    slide.addShape(pres.shapes.OVAL, {
      x: 0.45, y: yPos + 0.2, w: 0.48, h: 0.48,
      fill: { color: card.circleColor },
      line: { color: card.circleColor },
    });
    slide.addText(card.letter, {
      x: 0.45, y: yPos + 0.2, w: 0.48, h: 0.48,
      fontFace: "Georgia",
      fontSize: 18,
      bold: true,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 0,
    });

    // Title
    slide.addText(card.title, {
      x: 1.05, y: yPos + 0.08, w: 2.2, h: 0.38,
      fontFace: "Calibri",
      fontSize: 13,
      bold: true,
      color: C.navy,
      align: "left",
      valign: "middle",
      margin: 0,
    });

    // Description
    slide.addText(card.desc, {
      x: 3.35, y: yPos + 0.1, w: 6.3, h: cardH - 0.2,
      fontFace: "Calibri",
      fontSize: 12,
      color: "444444",
      align: "left",
      valign: "middle",
      margin: 0,
    });
  });
}

// ============================================================
// SLIDE 4 — LOS 5 PRINCIPIOS
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Title
  slide.addText("Los 5 principios del vendedor SPIN", {
    x: 0.5, y: 0.25, w: 9.0, h: 0.7,
    fontFace: "Georgia",
    fontSize: 30,
    bold: true,
    color: C.white,
    align: "left",
    margin: 0,
  });

  const principles = [
    { num: "1", name: "Actitud", desc: "Postura, sonrisa, CRM abierto. El cliente siente si estás presente o no." },
    { num: "2", name: "Preguntas > argumentos", desc: "Una buena pregunta vale más que diez argumentos." },
    { num: "3", name: "Una pregunta a la vez", desc: "Haz una, calla, escucha, reacciona. Nunca dos seguidas." },
    { num: "4", name: "El problema primero", desc: "Entiende qué le duele antes de mostrar la solución." },
    { num: "5", name: "Contexto antes que precio", desc: "Nunca des precio sin entender el negocio." },
  ];

  principles.forEach((p, i) => {
    const yPos = 1.05 + i * 0.88;

    // Number box
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 0.5, y: yPos, w: 0.35, h: 0.35,
      fill: { color: C.amber },
      line: { color: C.amber },
    });
    slide.addText(p.num, {
      x: 0.5, y: yPos, w: 0.35, h: 0.35,
      fontFace: "Calibri",
      fontSize: 13,
      bold: true,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 0,
    });

    // Principle name
    slide.addText(p.name, {
      x: 0.95, y: yPos, w: 2.8, h: 0.38,
      fontFace: "Calibri",
      fontSize: 14,
      bold: true,
      color: C.white,
      align: "left",
      valign: "middle",
      margin: 0,
    });

    // Description
    slide.addText(p.desc, {
      x: 3.85, y: yPos, w: 5.8, h: 0.38,
      fontFace: "Calibri",
      fontSize: 13,
      color: C.lightAccent,
      align: "left",
      valign: "middle",
      margin: 0,
    });

    // Separator line
    if (i < 4) {
      slide.addShape(pres.shapes.LINE, {
        x: 0.5, y: yPos + 0.48, w: 9.2, h: 0,
        line: { color: "2E4270", width: 0.8 },
      });
    }
  });
}

// ============================================================
// SLIDE 5 — TIPOS DE CLIENTE
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("¿Para qué necesitan la máquina?", {
    x: 0.25, y: 0.18, w: 9.5, h: 0.55,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText("Esto define todo: las preguntas, lo que muestras y cómo cierras.", {
    x: 0.25, y: 0.76, w: 9.5, h: 0.35,
    fontFace: "Calibri",
    fontSize: 13,
    color: C.muted,
    align: "left",
    margin: 0,
  });

  const clientCards = [
    { title: "Producir más sin contratar más gente", desc: "Productores, empanaderos en crecimiento, restaurantes con alta demanda" },
    { title: "Que el producto siempre salga igual", desc: "Cadenas, restaurantes con rotación, hoteles" },
    { title: "Bajar el costo por empanada", desc: "Productores con clientes grandes, cadenas" },
    { title: "Decirle que sí a pedidos grandes", desc: "Productores que venden a distribuidores, colegios, hospitales" },
    { title: "Ampliar el menú sin otro equipo", desc: "Restaurantes y cafeterías que quieren diversificarse" },
  ];

  // 2+2+1 grid layout
  const cardW = 4.55;
  const cardH = 0.88;
  const row1Y = 1.18;
  const row2Y = 2.12;
  const row3Y = 3.06;

  const positions = [
    { x: 0.25, y: row1Y },
    { x: 5.0,  y: row1Y },
    { x: 0.25, y: row2Y },
    { x: 5.0,  y: row2Y },
    { x: 2.6,  y: row3Y },
  ];

  clientCards.forEach((card, i) => {
    const pos = positions[i];
    const w = i === 4 ? 4.8 : cardW;

    slide.addShape(pres.shapes.RECTANGLE, {
      x: pos.x, y: pos.y, w: w, h: cardH,
      fill: { color: C.white },
      line: { color: "DDDDDD", width: 0.5 },
      shadow: makeShadow(),
    });
    // Amber top border
    slide.addShape(pres.shapes.RECTANGLE, {
      x: pos.x, y: pos.y, w: w, h: 0.06,
      fill: { color: C.amber },
      line: { color: C.amber },
    });
    slide.addText(card.title, {
      x: pos.x + 0.1, y: pos.y + 0.1, w: w - 0.2, h: 0.36,
      fontFace: "Calibri",
      fontSize: 12,
      bold: true,
      color: C.navy,
      align: "left",
      margin: 0,
    });
    slide.addText(card.desc, {
      x: pos.x + 0.1, y: pos.y + 0.48, w: w - 0.2, h: 0.35,
      fontFace: "Calibri",
      fontSize: 11,
      color: C.muted,
      align: "left",
      margin: 0,
    });
  });

  // Amber bottom callout
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.25, y: 4.05, w: 9.5, h: 0.55,
    fill: { color: C.lightAccent },
    line: { color: C.lightAccent },
  });
  slide.addText(
    "Escucha la primera respuesta larga del cliente. Ahí está la respuesta.",
    {
      x: 0.3, y: 4.05, w: 9.4, h: 0.55,
      fontFace: "Calibri",
      fontSize: 12,
      italic: true,
      color: C.navy,
      align: "center",
      valign: "middle",
      margin: 5,
    }
  );
}

// ============================================================
// SLIDE 6 — LA LLAMADA: 7 PASOS
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Title
  slide.addText("La llamada en 7 pasos", {
    x: 0.5, y: 0.2, w: 9.2, h: 0.65,
    fontFace: "Georgia",
    fontSize: 28,
    bold: true,
    color: C.white,
    align: "left",
    margin: 0,
  });

  // Vertical amber connector line
  slide.addShape(pres.shapes.LINE, {
    x: 0.9, y: 0.92, w: 0, h: 4.4,
    line: { color: C.amber, width: 2 },
  });

  const steps = [
    { num: "1", name: "APERTURA", time: "(30 seg)", desc: "Generar confianza desde el primer segundo" },
    { num: "2", name: "ENTENDER EL NEGOCIO", time: "(4–6 min)", desc: "Los 4 niveles SPIN en orden" },
    { num: "3", name: "PRUEBA SOCIAL", time: "(2 min)", desc: "El cliente que más se parece al tuyo" },
    { num: "4", name: "SOLUCIÓN Y PRECIO", time: "(2–3 min)", desc: "Solo lo que resuelve el problema que encontraste" },
    { num: "5", name: "CIERRE", time: "", desc: "Siempre termina con un paso concreto" },
    { num: "6", name: "DEMO / COTIZACIÓN / SEGUIMIENTO", time: "", desc: "Según la temperatura del cliente" },
    { num: "7", name: "CUANDO HAY VENTA", time: "", desc: "Dejar todo claro, confirmar sin ambigüedad" },
  ];

  steps.forEach((step, i) => {
    const yPos = 0.88 + i * 0.62;

    // Circle on the line
    slide.addShape(pres.shapes.OVAL, {
      x: 0.7, y: yPos + 0.08, w: 0.4, h: 0.4,
      fill: { color: C.amber },
      line: { color: C.amber },
    });
    slide.addText(step.num, {
      x: 0.7, y: yPos + 0.08, w: 0.4, h: 0.4,
      fontFace: "Calibri",
      fontSize: 12,
      bold: true,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 0,
    });

    // Step name
    slide.addText(step.name + (step.time ? " " + step.time : ""), {
      x: 1.22, y: yPos + 0.02, w: 3.8, h: 0.35,
      fontFace: "Calibri",
      fontSize: 12,
      bold: true,
      color: C.white,
      align: "left",
      valign: "middle",
      margin: 0,
    });

    // Description
    slide.addText(step.desc, {
      x: 5.2, y: yPos + 0.02, w: 4.5, h: 0.35,
      fontFace: "Calibri",
      fontSize: 12,
      color: C.lightAccent,
      align: "left",
      valign: "middle",
      margin: 0,
    });
  });
}

// ============================================================
// SLIDE 7 — IMPLICACIÓN
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("Implicación: la etapa más importante", {
    x: 0.25, y: 0.18, w: 9.5, h: 0.55,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText(
    "Un problema pequeño se ignora. Las preguntas de implicación revelan cuánto cuesta en realidad.",
    {
      x: 0.25, y: 0.76, w: 9.5, h: 0.35,
      fontFace: "Calibri",
      fontSize: 12,
      color: C.muted,
      align: "left",
      margin: 0,
    }
  );

  // Left column — Operador
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.25, y: 1.2, w: 4.5, h: 0.42,
    fill: { color: C.navy },
    line: { color: C.navy },
  });
  slide.addText("Operador (trabaja en producción)", {
    x: 0.25, y: 1.2, w: 4.5, h: 0.42,
    fontFace: "Calibri",
    fontSize: 12,
    bold: true,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  const operatorRows = [
    { dijo: "No damos abasto", pregunta: "¿Cuántas empanadas dejan de hacer? ¿Cuánto es eso al mes?" },
    { dijo: "Rechazamos pedidos grandes", pregunta: "¿Cuántos pedidos así han dicho que no este año?" },
    { dijo: "Dependemos del que sabe", pregunta: "¿Qué pasa cuando esa persona no llega?" },
  ];

  operatorRows.forEach((row, i) => {
    const y = 1.65 + i * 0.82;
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 0.25, y: y, w: 4.5, h: 0.75,
      fill: { color: C.white },
      line: { color: "DDDDDD", width: 0.5 },
    });
    slide.addText([
      { text: "Si dijo: ", options: { bold: true, color: C.amber } },
      { text: row.dijo, options: { color: "444444" } },
    ], {
      x: 0.3, y: y + 0.03, w: 4.35, h: 0.3,
      fontFace: "Calibri",
      fontSize: 11,
      margin: 3,
    });
    slide.addText([
      { text: "→ ", options: { bold: true, color: C.navy } },
      { text: row.pregunta, options: { italic: true, color: C.navy } },
    ], {
      x: 0.3, y: y + 0.36, w: 4.35, h: 0.33,
      fontFace: "Calibri",
      fontSize: 11,
      margin: 3,
    });
  });

  // Right column — Inversionista
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 5.0, y: 1.2, w: 4.75, h: 0.42,
    fill: { color: C.navy },
    line: { color: C.navy },
  });
  slide.addText("Inversionista (dueño, no produce)", {
    x: 5.0, y: 1.2, w: 4.75, h: 0.42,
    fontFace: "Calibri",
    fontSize: 12,
    bold: true,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  const investorRows = [
    { dijo: "El proceso no escala", pregunta: "¿Siente que el negocio tiene un techo con ese proceso?" },
    { dijo: "Los números no dan", pregunta: "¿Qué tendría que cambiar para que sí dieran?" },
  ];

  investorRows.forEach((row, i) => {
    const y = 1.65 + i * 0.82;
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 5.0, y: y, w: 4.75, h: 0.75,
      fill: { color: C.white },
      line: { color: "DDDDDD", width: 0.5 },
    });
    slide.addText([
      { text: "Si dijo: ", options: { bold: true, color: C.amber } },
      { text: row.dijo, options: { color: "444444" } },
    ], {
      x: 5.05, y: y + 0.03, w: 4.6, h: 0.3,
      fontFace: "Calibri",
      fontSize: 11,
      margin: 3,
    });
    slide.addText([
      { text: "→ ", options: { bold: true, color: C.navy } },
      { text: row.pregunta, options: { italic: true, color: C.navy } },
    ], {
      x: 5.05, y: y + 0.36, w: 4.6, h: 0.33,
      fontFace: "Calibri",
      fontSize: 11,
      margin: 3,
    });
  });

  // Bottom callout
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.25, y: 4.62, w: 9.5, h: 0.85,
    fill: { color: C.lightAccent },
    line: { color: C.lightAccent },
  });
  slide.addText(
    "Cuando confirme el problema: 'Lo que me está diciendo es que el proceso manual no es el problema de hoy — es lo que le pone techo al negocio mañana.'",
    {
      x: 0.35, y: 4.62, w: 9.3, h: 0.85,
      fontFace: "Calibri",
      fontSize: 11,
      italic: true,
      color: C.navy,
      align: "center",
      valign: "middle",
      margin: 5,
    }
  );
}

// ============================================================
// SLIDE 8 — LAS MÁQUINAS
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("¿Qué máquina ofrecer?", {
    x: 0.25, y: 0.18, w: 9.5, h: 0.55,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText("La decisión parte del volumen y el tipo de masa — no del precio.", {
    x: 0.25, y: 0.76, w: 9.5, h: 0.35,
    fontFace: "Calibri",
    fontSize: 13,
    color: C.muted,
    align: "left",
    margin: 0,
  });

  // Table
  const headerRow = [
    { text: "Máquina", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 12 } },
    { text: "Emp/hora", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 12 } },
    { text: "Masas y productos", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 12 } },
    { text: "Para quién", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 12 } },
  ];

  const machineData = [
    ["CM06", "500", "Maíz · Arepas", "Inicio, volumen bajo"],
    ["CM06B", "500", "Maíz · Arepas rellenas · Patacones · Aborrajados · Pasteles", "Inicio con portafolio variado"],
    ["CM07", "400", "Solo trigo", "Especializado en trigo"],
    ["CM08", "500", "Trigo · Maíz · Arepas · múltiples productos", "Mediano con menú variado"],
    ["CM05S", "1.600", "Trigo · Maíz · múltiples", "Crecimiento fuerte o industrial"],
    ["CM05i", "1.600", "Igual que CM05S + control remoto", "Dueño no siempre presente, varios locales"],
  ];

  const tableData = [headerRow];
  machineData.forEach((row, i) => {
    const bgColor = i % 2 === 0 ? C.white : C.lightBg;
    tableData.push(
      row.map(cell => ({
        text: cell,
        options: { fill: { color: bgColor }, color: "333333", fontSize: 11 },
      }))
    );
  });

  slide.addTable(tableData, {
    x: 0.25, y: 1.18, w: 9.5, h: 3.4,
    border: { pt: 0.5, color: "DDDDDD" },
    colW: [1.2, 1.0, 4.3, 3.0],
    rowH: 0.48,
  });

  // Formula box
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.25, y: 4.75, w: 9.5, h: 0.55,
    fill: { color: C.lightAccent },
    line: { color: C.lightAccent },
  });
  slide.addText(
    "Meses de recuperación = precio máquina ÷ ahorro mensual en personal",
    {
      x: 0.3, y: 4.75, w: 9.4, h: 0.55,
      fontFace: "Calibri",
      fontSize: 12,
      bold: true,
      color: C.navy,
      align: "center",
      valign: "middle",
      margin: 5,
    }
  );
}

// ============================================================
// SLIDE 9 — OBJECIONES
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Title
  slide.addText("Cómo responder las objeciones", {
    x: 0.5, y: 0.18, w: 9.2, h: 0.62,
    fontFace: "Georgia",
    fontSize: 28,
    bold: true,
    color: C.white,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText("Primero dale la razón. Nunca contradigas de frente.", {
    x: 0.5, y: 0.82, w: 9.2, h: 0.35,
    fontFace: "Calibri",
    fontSize: 13,
    color: C.lightAccent,
    align: "left",
    margin: 0,
  });

  const objections = [
    { obj: "\"Es muy caro\"", resp: "Sí, es una inversión. ¿Le muestro cómo otros calcularon cuándo se les pagaba sola?" },
    { obj: "\"Ahorita no es el momento\"", resp: "¿Qué tendría que pasar para que sea el momento?" },
    { obj: "\"Lo tengo que consultar\"", resp: "¿Con quién lo va a hablar? ¿Le preparo un resumen para esa conversación?" },
    { obj: "\"Ya tenemos proveedor\"", resp: "¿Qué tan contentos están con la capacidad y consistencia?" },
    { obj: "\"No conocemos la marca\"", resp: "15 años fabricando, 5 patentes, CE y FDA. ¿Una demo sin compromiso?" },
    { obj: "\"No tenemos espacio\"", resp: "¿Cuánto tienen? Nuestros modelos arrancan desde 1 m²." },
  ];

  const cardW = 4.45;
  const cardH = 1.05;
  const col1X = 0.5;
  const col2X = 5.2;

  objections.forEach((o, i) => {
    const col = i % 2;
    const row = Math.floor(i / 2);
    const xPos = col === 0 ? col1X : col2X;
    const yPos = 1.25 + row * 1.12;

    slide.addShape(pres.shapes.RECTANGLE, {
      x: xPos, y: yPos, w: cardW, h: cardH,
      fill: { color: C.darkNavy2 },
      line: { color: C.darkNavy2 },
    });
    // Amber left border
    slide.addShape(pres.shapes.RECTANGLE, {
      x: xPos, y: yPos, w: 0.07, h: cardH,
      fill: { color: C.amber },
      line: { color: C.amber },
    });
    slide.addText(o.obj, {
      x: xPos + 0.12, y: yPos + 0.05, w: cardW - 0.18, h: 0.38,
      fontFace: "Calibri",
      fontSize: 12,
      bold: true,
      color: C.amber,
      align: "left",
      valign: "middle",
      margin: 3,
    });
    slide.addText(o.resp, {
      x: xPos + 0.12, y: yPos + 0.45, w: cardW - 0.18, h: 0.55,
      fontFace: "Calibri",
      fontSize: 11,
      color: C.white,
      align: "left",
      valign: "top",
      margin: 3,
    });
  });
}

// ============================================================
// SLIDE 10 — TEMPERATURA Y SEÑALES
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("Leer la temperatura del cliente", {
    x: 0.25, y: 0.18, w: 9.5, h: 0.55,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  const colW = 2.98;
  const colGap = 0.25;
  const col1X = 0.25;
  const col2X = col1X + colW + colGap;
  const col3X = col2X + colW + colGap;

  const columns = [
    {
      label: "CALIENTE",
      headerColor: C.red,
      signals: [
        "Pregunta el precio después del diagnóstico",
        "'Eso es justo lo que necesitamos'",
        "Pregunta por garantía, capacitación o instalación",
        "Mete a un socio o jefe en la llamada",
      ],
      action: "Demo o cierre esta semana",
    },
    {
      label: "TIBIO",
      headerColor: C.amber,
      signals: [
        "'Ahorita no' pero entiende el problema",
        "No decide solo",
      ],
      action: "Seguimiento en 2 semanas",
    },
    {
      label: "FRÍO",
      headerColor: C.navy,
      signals: [
        "No tiene claro el problema",
      ],
      action: "Campaña, retomar en meses",
    },
  ];

  const colXs = [col1X, col2X, col3X];

  columns.forEach((col, i) => {
    const xPos = colXs[i];

    // Header
    slide.addShape(pres.shapes.RECTANGLE, {
      x: xPos, y: 0.85, w: colW, h: 0.45,
      fill: { color: col.headerColor },
      line: { color: col.headerColor },
    });
    slide.addText(col.label, {
      x: xPos, y: 0.85, w: colW, h: 0.45,
      fontFace: "Calibri",
      fontSize: 13,
      bold: true,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 0,
    });

    // Signal items
    col.signals.forEach((signal, j) => {
      const y = 1.35 + j * 0.68;
      slide.addShape(pres.shapes.RECTANGLE, {
        x: xPos, y: y, w: colW, h: 0.6,
        fill: { color: C.white },
        line: { color: "DDDDDD", width: 0.5 },
        shadow: makeShadow(),
      });
      slide.addText(signal, {
        x: xPos + 0.1, y: y, w: colW - 0.15, h: 0.6,
        fontFace: "Calibri",
        fontSize: 11,
        color: "333333",
        align: "left",
        valign: "middle",
        margin: 5,
      });
    });

    // Action at bottom
    slide.addText(col.action, {
      x: xPos, y: 4.88, w: colW, h: 0.45,
      fontFace: "Calibri",
      fontSize: 11,
      italic: true,
      bold: true,
      color: col.headerColor,
      align: "center",
      valign: "middle",
      margin: 0,
    });
  });
}

// ============================================================
// SLIDE 11 — LO QUE NO FUNCIONA vs LO QUE SÍ
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Title
  slide.addText("Antes y después del método", {
    x: 0.5, y: 0.18, w: 9.2, h: 0.62,
    fontFace: "Georgia",
    fontSize: 28,
    bold: true,
    color: C.white,
    align: "left",
    margin: 0,
  });

  // Left column header — Sin método
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0.3, y: 0.88, w: 4.35, h: 0.45,
    fill: { color: C.red },
    line: { color: C.red },
  });
  slide.addText("Sin método", {
    x: 0.3, y: 0.88, w: 4.35, h: 0.45,
    fontFace: "Calibri",
    fontSize: 13,
    bold: true,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  // Right column header — Con método SPIN
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 5.35, y: 0.88, w: 4.35, h: 0.45,
    fill: { color: C.green },
    line: { color: C.green },
  });
  slide.addText("Con método SPIN", {
    x: 5.35, y: 0.88, w: 4.35, h: 0.45,
    fontFace: "Calibri",
    fontSize: 13,
    bold: true,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  // Vertical divider
  slide.addShape(pres.shapes.LINE, {
    x: 5.0, y: 0.88, w: 0, h: 4.5,
    line: { color: C.white, width: 0.8 },
  });

  const leftItems = [
    "\"Le quiero vender una máquina\"",
    "\"Tenemos la mejor máquina del mercado\"",
    "\"¿Cuándo me da una respuesta?\"",
    "\"No se preocupe por el precio\"",
    "\"¿Le interesa?\"",
    "Decirle los beneficios al cliente",
  ];

  const rightItems = [
    "\"Quisiera entender cómo están trabajando hoy\"",
    "\"Otros con el mismo problema lograron [resultado concreto]\"",
    "\"¿Qué necesitaría ver para sentirse tranquilo de avanzar?\"",
    "\"Hablemos primero de lo que resuelve\"",
    "\"¿Es algo así lo que usted necesita?\"",
    "Preguntarle al cliente qué cambiaría si el problema ya no existiera",
  ];

  leftItems.forEach((item, i) => {
    slide.addText(item, {
      x: 0.45, y: 1.4 + i * 0.55, w: 4.35, h: 0.5,
      fontFace: "Calibri",
      fontSize: 11,
      color: "FFAAAA",
      align: "left",
      valign: "middle",
      margin: 3,
    });
  });

  rightItems.forEach((item, i) => {
    slide.addText(item, {
      x: 5.15, y: 1.4 + i * 0.55, w: 4.45, h: 0.5,
      fontFace: "Calibri",
      fontSize: 11,
      color: "AAFFAA",
      align: "left",
      valign: "middle",
      margin: 3,
    });
  });
}

// ============================================================
// SLIDE 12 — CHECKLIST POST-LLAMADA
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.lightBg };

  // Left vertical amber bar
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 0.12, h: 5.625,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Title
  slide.addText("Checklist al colgar", {
    x: 0.25, y: 0.18, w: 9.5, h: 0.55,
    fontFace: "Georgia",
    fontSize: 26,
    bold: true,
    color: C.navy,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText("Completa en el CRM justo después de cada llamada.", {
    x: 0.25, y: 0.76, w: 9.5, h: 0.35,
    fontFace: "Calibri",
    fontSize: 13,
    color: C.muted,
    align: "left",
    margin: 0,
  });

  const checkItems = [
    "¿Identifiqué el problema principal?",
    "¿Lo conecté con la solución?",
    "¿El cliente dijo con sus palabras el beneficio que busca?",
    "¿Hay un siguiente paso agendado con fecha y hora?",
    "¿Sé quién decide la compra?",
  ];

  checkItems.forEach((item, i) => {
    const yPos = 1.18 + i * 0.62;

    // Checkbox shape
    slide.addShape(pres.shapes.RECTANGLE, {
      x: 0.3, y: yPos + 0.08, w: 0.38, h: 0.38,
      fill: { color: C.amber },
      line: { color: C.amber },
    });
    slide.addText("✓", {
      x: 0.3, y: yPos + 0.08, w: 0.38, h: 0.38,
      fontFace: "Calibri",
      fontSize: 14,
      bold: true,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 0,
    });

    slide.addText(item, {
      x: 0.82, y: yPos + 0.06, w: 8.8, h: 0.42,
      fontFace: "Calibri",
      fontSize: 14,
      color: C.navy,
      align: "left",
      valign: "middle",
      margin: 0,
    });
  });

  // Field reference table
  const fieldHeaders = [
    { text: "Para qué necesita la máquina", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 10 } },
    { text: "Problema principal", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 10 } },
    { text: "Costo del problema", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 10 } },
    { text: "Temperatura", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 10 } },
    { text: "Siguiente paso", options: { fill: { color: C.navy }, color: C.white, bold: true, fontSize: 10 } },
  ];

  const fieldValues = [
    [
      { text: "_______________", options: { fill: { color: C.white }, color: "AAAAAA", fontSize: 10 } },
      { text: "_______________", options: { fill: { color: C.white }, color: "AAAAAA", fontSize: 10 } },
      { text: "_______________", options: { fill: { color: C.white }, color: "AAAAAA", fontSize: 10 } },
      { text: "_______________", options: { fill: { color: C.white }, color: "AAAAAA", fontSize: 10 } },
      { text: "_______________", options: { fill: { color: C.white }, color: "AAAAAA", fontSize: 10 } },
    ],
  ];

  slide.addTable([fieldHeaders, ...fieldValues], {
    x: 0.25, y: 4.72, w: 9.5, h: 0.72,
    border: { pt: 0.5, color: "DDDDDD" },
    colW: [2.1, 2.0, 1.8, 1.5, 2.1],
    rowH: 0.36,
  });
}

// ============================================================
// SLIDE 13 — RESPALDO DE MARCA
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Title
  slide.addText("El respaldo detrás de cada venta", {
    x: 0.5, y: 0.2, w: 9.2, h: 0.65,
    fontFace: "Georgia",
    fontSize: 28,
    bold: true,
    color: C.white,
    align: "left",
    margin: 0,
  });

  // Subtitle
  slide.addText(
    "Úsalo cuando el cliente dude de la calidad o no conozca la marca.",
    {
      x: 0.5, y: 0.88, w: 9.2, h: 0.38,
      fontFace: "Calibri",
      fontSize: 13,
      color: C.lightAccent,
      align: "left",
      margin: 0,
    }
  );

  const stats = [
    { stat: "15 años", label: "fabricando maquinaria para empanadas" },
    { stat: "5 patentes", label: "diseño y tecnología propios" },
    { stat: "CE + FDA", label: "certificaciones industriales y alimentarias" },
    { stat: "+40 países", label: "despacho internacional con DHL" },
    { stat: "Fábrica", label: "Manizales · Showroom Miami" },
  ];

  const cardW = 1.82;
  const statCardH = 2.0;
  const startX = 0.3;
  const gap = 0.12;

  stats.forEach((s, i) => {
    const xPos = startX + i * (cardW + gap);

    slide.addShape(pres.shapes.RECTANGLE, {
      x: xPos, y: 1.4, w: cardW, h: statCardH,
      fill: { color: C.darkNavy2 },
      line: { color: C.amber, width: 1 },
    });

    slide.addText(s.stat, {
      x: xPos, y: 1.55, w: cardW, h: 0.75,
      fontFace: "Georgia",
      fontSize: 24,
      bold: true,
      color: C.amber,
      align: "center",
      valign: "middle",
      margin: 0,
    });

    slide.addText(s.label, {
      x: xPos + 0.08, y: 2.38, w: cardW - 0.16, h: 0.88,
      fontFace: "Calibri",
      fontSize: 11,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 3,
    });
  });

  // Bottom quote
  slide.addText(
    "Sus clientes más exigentes no van a tener ningún reparo.",
    {
      x: 0.5, y: 4.8, w: 9.2, h: 0.55,
      fontFace: "Calibri",
      fontSize: 13,
      italic: true,
      color: C.amber,
      align: "center",
      valign: "middle",
      margin: 0,
    }
  );
}

// ============================================================
// SLIDE 14 — CIERRE
// ============================================================
{
  let slide = pres.addSlide();
  slide.background = { color: C.navy };

  // Decorative amber rectangle top-left
  slide.addShape(pres.shapes.RECTANGLE, {
    x: 0, y: 0, w: 2.0, h: 1.2,
    fill: { color: C.amber },
    line: { color: C.amber },
  });

  // Large quote text
  slide.addText(
    "Cada llamada es una oportunidad de ayudar a alguien a darse cuenta de algo que ya sabía pero no había articulado.",
    {
      x: 0.8, y: 1.4, w: 8.4, h: 1.8,
      fontFace: "Georgia",
      fontSize: 24,
      italic: true,
      color: C.white,
      align: "center",
      valign: "middle",
      margin: 0,
    }
  );

  // Thin amber horizontal line
  slide.addShape(pres.shapes.LINE, {
    x: 2.5, y: 3.4, w: 5.0, h: 0,
    line: { color: C.amber, width: 2 },
  });

  // Below line text
  slide.addText("Metodología SPIN · Maquiempanadas · 2026", {
    x: 1.0, y: 3.6, w: 8.0, h: 0.5,
    fontFace: "Calibri",
    fontSize: 16,
    color: C.white,
    align: "center",
    valign: "middle",
    margin: 0,
  });

  // Bottom right small text
  slide.addText("Entregable de consultoría", {
    x: 6.5, y: 5.1, w: 3.2, h: 0.35,
    fontFace: "Calibri",
    fontSize: 10,
    color: C.lightAccent,
    align: "right",
    margin: 0,
  });
}

// ============================================================
// WRITE FILE
// ============================================================
pres
  .writeFile({ fileName: "/Users/projects/ari-wirechat/.ia/maquiempanadas_metodo_ventas.pptx" })
  .then(() => {
    console.log("✅ Presentation created: /Users/projects/ari-wirechat/.ia/maquiempanadas_metodo_ventas.pptx");
  })
  .catch((err) => {
    console.error("❌ Error:", err);
    process.exit(1);
  });
