# Guía de Integración

## Integración con CRM / SellerChat

Por defecto la app no guarda leads en ningún servicio externo. Si quieres enviarlos a tu CRM o a SellerChat, usa tu propio endpoint (REST, webhook, Zapier, etc.). El objeto que captura el frontend incluye:

- `country`, `currency`, `manualDailyProduction`, `manualEmpanadasPerHour`, `machineEmpanadasPerHour`, `hourlyWage`, `dailyHours`, `machineCost`, `phone_number`
- Métricas calculadas en el paso final (payback, ahorros, eficiencia) si vuelves a ejecutar `calculateROI` en tu backend.

### Ejemplo de POST a tu API

```typescript
const sendLead = async (leadData) => {
  await fetch(process.env.LEAD_WEBHOOK_URL!, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(leadData),
  });
};
```

### Ejemplo de integración con SellerChat

```typescript
// En src/components/results/LeadCapture.tsx, después de guardar en DB:

const sendToSellerChat = async (leadData) => {
  await fetch('https://api.sellerchat.com/leads', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_API_KEY',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      phone: leadData.phone_number,
      name: 'Lead Quiz Empanadas',
      custom_fields: {
        country: leadData.country,
        payback_months: leadData.payback_months,
        monthly_savings: leadData.monthly_savings,
        annual_savings: leadData.annual_savings,
      },
    }),
  });
};
```

### Envío de Reporte por WhatsApp

Para enviar el diagnóstico completo por WhatsApp:

1. **Usando WhatsApp Business API:**
   ```typescript
   const sendWhatsAppReport = async (phone, reportData) => {
     const message = `
   🎯 Tu Diagnóstico de ROI - Máquina de Empanadas

   ⏱️ Tiempo de Recuperación: ${reportData.payback_months} meses
   💰 Ahorro Mensual: ${reportData.monthly_savings}
   📈 Ahorro Anual: ${reportData.annual_savings}
   ⚡ Incremento de Eficiencia: +${reportData.efficiency_increase_percent}%

   ¿Quieres más detalles? Responde a este mensaje.
     `;

     await fetch('https://api.whatsapp.com/send', {
       method: 'POST',
       headers: {
         'Authorization': 'Bearer YOUR_WHATSAPP_TOKEN',
         'Content-Type': 'application/json',
       },
       body: JSON.stringify({
         to: phone,
         type: 'text',
         text: { body: message },
       }),
     });
   };
   ```

2. **Usando servicio de SMS alternativo (Twilio, etc.):**
   Similar al anterior pero usando la API de tu proveedor de SMS.

### Exportar a PDF

La función de exportar PDF usa `window.print()` que abre el diálogo de impresión del navegador.

Para generar PDF programáticamente:

1. Instala una librería como `jspdf` o `html2canvas`
2. Modifica `FullReport.tsx`:

```typescript
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

const handleExportPDF = async () => {
  const element = document.getElementById('report-content');
  const canvas = await html2canvas(element);
  const imgData = canvas.toDataURL('image/png');
  const pdf = new jsPDF();
  pdf.addImage(imgData, 'PNG', 0, 0);
  pdf.save('diagnostico-roi.pdf');
};
```

### Personalización de Monedas y Países

Para añadir más países o ajustar configuraciones:

Edita `src/config/countries.ts`:

```typescript
export const COUNTRIES: CountryConfig[] = [
  // ... países existentes
  {
    code: 'ES',
    name: 'España',
    currency: 'EUR',
    currencySymbol: '€',
    suggestedHourlyWage: 12,
  },
];
```

### Tracking y Analytics

Para añadir tracking de eventos:

```typescript
// Ejemplo con Google Analytics
const trackQuizComplete = (leadData) => {
  gtag('event', 'quiz_complete', {
    country: leadData.country,
    payback_months: leadData.payback_months,
    value: leadData.annual_savings,
  });
};
```

### Soporte Multi-idioma

Para añadir múltiples idiomas, usa una librería como `react-i18next`:

```bash
npm install react-i18next i18next
```

Y configura traducciones en `src/locales/`.
