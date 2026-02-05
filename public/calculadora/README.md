# Calculadora de Recuperación de Inversión - Máquina de Empanadas

Calculadora interactiva para estimar el tiempo de recuperación de la inversión y el ahorro con la automatización de empanadas.

## Características

### 🎯 Etapa 1: Cuestionario Interactivo
- 7 preguntas paso a paso con navegación fluida
- Validación de datos en tiempo real
- Soporte para 6 países y monedas (COP, USD, MXN, CLP, PEN, ARS)
- Sugerencias inteligentes basadas en valores comunes
- Barra de progreso visual

### 📊 Etapa 2: Cálculo Automático
Fórmulas precisas para calcular:
- Costo por empanada (manual vs máquina)
- Ahorro diario, mensual y anual
- Tiempo de recuperación de inversión (Payback)
- Incremento de eficiencia en porcentaje
- Validación de viabilidad de inversión

### 🔒 Etapa 3: Generación de Leads
- Resultados parciales para generar interés
- Captura de número de WhatsApp con validación
- Envío opcional a CRM vía endpoint configurable
- Reporte completo con análisis detallado
- Recomendaciones personalizadas según perfil (Startup/Growth/Expanding)

## Instalación

```bash
npm install
```

## Configuración

Variables opcionales:

```env
VITE_CRM_API_URL=https://api.maquiempanadas.com
VITE_ESCALABLE_ENDPOINT= # Si quieres un endpoint distinto al de arriba
```

Si no defines nada, se usa `https://api.maquiempanadas.com/api/quizzes/escalable` como destino para enviar el lead con los cálculos.

## Uso

### Desarrollo
```bash
npm run dev
```

### Producción
```bash
npm run build
npm run preview
```

### Type Checking
```bash
npm run typecheck
```

## Estructura del Proyecto

```
src/
├── components/
│   ├── QuizContainer.tsx          # Contenedor principal
│   ├── ProgressBar.tsx            # Barra de progreso
│   ├── steps/                     # Componentes de cada pregunta
│   │   ├── CountryStep.tsx
│   │   ├── ManualProductionStep.tsx
│   │   ├── ManualRateStep.tsx
│   │   ├── MachineRateStep.tsx
│   │   ├── HourlyWageStep.tsx
│   │   ├── DailyHoursStep.tsx
│   │   └── MachineCostStep.tsx
│   └── results/                   # Componentes de resultados
│       ├── PartialResults.tsx     # Vista previa bloqueada
│       ├── LeadCapture.tsx        # Captura de WhatsApp
│       └── FullReport.tsx         # Reporte completo
├── config/
│   └── countries.ts               # Configuración de países y monedas
├── hooks/
│   └── useQuizState.ts            # State management del quiz
├── utils/
│   └── calculations.ts            # Lógica de cálculos y formateo
└── App.tsx                        # Componente raíz
```

## Persistencia

Los datos capturados se usan únicamente en el cliente para generar el reporte. Si necesitas guardarlos en un backend o enviarlos a un CRM, conecta tu propia API siguiendo los ejemplos de `INTEGRATION_GUIDE.md`.

## Fórmulas de Cálculo

```javascript
const DIAS_MES = 20; // 5 días/semana * 4 semanas
costo_manual_por_emp = salario_hora / manual_emp_hora
costo_maquina_por_emp = salario_hora / maquina_emp_hora
ahorro_por_emp = costo_manual_por_emp - costo_maquina_por_emp
ahorro_diario = ahorro_por_emp * produccion_diaria
ahorro_mensual = ahorro_diario * DIAS_MES
payback_meses = costo_maquina / ahorro_mensual
ganancia_anual = ahorro_mensual * 12
eficiencia = ((maquina_emp_hora - manual_emp_hora) / manual_emp_hora) * 100
```

## Personalización

### Agregar Nuevos Países

Edita `src/config/countries.ts`:

```typescript
export const COUNTRIES: CountryConfig[] = [
  // ...países existentes
  {
    code: 'BR',
    name: 'Brasil',
    currency: 'BRL',
    currencySymbol: 'R$',
    suggestedHourlyWage: 15,
  },
];
```

### Modificar Modelos de Máquina

En `src/config/countries.ts`:

```typescript
export const MACHINE_MODELS = [
  { name: 'Básica', empanadasPerHour: 600, suggestedPrice: 5000 },
  { name: 'Premium', empanadasPerHour: 3000, suggestedPrice: 35000 },
];
```

### Cambiar Días Laborables

```typescript
export const DAYS_PER_MONTH = 22; // Modifica según tu caso
```

## Integraciones

### Envío a CRM

- Los leads se envían por POST a `VITE_ESCALABLE_ENDPOINT` (o `https://api.maquiempanadas.com/api/quizzes/escalable` por defecto) con el teléfono, payback, ahorro mensual/anual y las respuestas del quiz. Implementación en `src/services/api.ts`.

### Exportar PDF

El botón "Descargar PDF" usa `window.print()` del navegador. Para generar PDFs programáticamente, considera usar:
- `jspdf`
- `html2canvas`
- `react-pdf`

## Inserción en Landing Pages

### Como iframe:
```html
<iframe
  src="https://tu-dominio.com"
  width="100%"
  height="800px"
  frameborder="0"
></iframe>
```

### Como componente embebido:
```html
<div id="quiz-root"></div>
<script src="https://tu-dominio.com/embed.js"></script>
```

## Tecnologías Utilizadas

- **React 18** - Framework UI
- **TypeScript** - Type safety
- **Tailwind CSS** - Styling
- **Vite** - Build tool
- **Lucide React** - Iconos
- **react-phone-number-input** - Selector de teléfono con prefijos

## Características de Diseño

- Diseño responsive (mobile-first)
- Gradientes modernos (azul y verde)
- Animaciones suaves de transición
- Estados de hover y focus bien definidos
- Validación en tiempo real
- Mensajes de error claros
- UI accesible y fácil de usar

## Licencia

Propietario - Todos los derechos reservados
