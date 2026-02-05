# Integración con SellerChat

Guía completa para integrar el Quiz Funnel con SellerChat.

## Opción 1: Webhook a SellerChat

### Paso 1: Crear Edge Function que envía a SellerChat

```typescript
import "jsr:@supabase/functions-js/edge-runtime.d.ts";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "POST, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type, Authorization, X-Client-Info, Apikey",
};

interface LeadData {
  phone_number: string;
  country: string;
  currency: string;
  payback_months: number;
  monthly_savings: number;
  annual_savings: number;
  efficiency_increase_percent: number;
}

async function sendToSellerChat(data: LeadData) {
  const sellerChatApiKey = Deno.env.get("SELLERCHAT_API_KEY");
  const sellerChatUrl = Deno.env.get("SELLERCHAT_WEBHOOK_URL");

  if (!sellerChatApiKey || !sellerChatUrl) {
    throw new Error("SellerChat credentials not configured");
  }

  const payload = {
    phone: data.phone_number,
    name: `Lead Quiz - ${data.country}`,
    tags: ["quiz_roi", data.country.toLowerCase()],
    custom_fields: {
      pais: data.country,
      moneda: data.currency,
      payback_meses: data.payback_months.toFixed(1),
      ahorro_mensual: data.monthly_savings.toFixed(2),
      ahorro_anual: data.annual_savings.toFixed(2),
      eficiencia_incremento: data.efficiency_increase_percent.toFixed(1),
      perfil: data.payback_months <= 6 ? "Startup" : data.payback_months <= 12 ? "Growth" : "Expanding",
    },
    initial_message: `
🎯 Nuevo Lead del Quiz ROI

Tiempo de recuperación: ${data.payback_months.toFixed(1)} meses
Ahorro mensual: ${data.currency}${data.monthly_savings.toFixed(2)}
Ahorro anual: ${data.currency}${data.annual_savings.toFixed(2)}

Este lead ha completado el quiz y está interesado en automatizar su producción.
    `.trim(),
  };

  const response = await fetch(sellerChatUrl, {
    method: "POST",
    headers: {
      "Authorization": `Bearer ${sellerChatApiKey}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    const error = await response.text();
    throw new Error(`SellerChat API error: ${error}`);
  }

  return await response.json();
}

Deno.serve(async (req: Request) => {
  if (req.method === "OPTIONS") {
    return new Response(null, { status: 200, headers: corsHeaders });
  }

  try {
    const leadData: LeadData = await req.json();

    const result = await sendToSellerChat(leadData);

    console.log("✅ Lead sent to SellerChat:", result);

    return new Response(
      JSON.stringify({
        success: true,
        message: "Lead sent to SellerChat successfully",
        sellerchat_response: result,
      }),
      {
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  } catch (error) {
    console.error("Error:", error);

    return new Response(
      JSON.stringify({
        success: false,
        error: error.message,
      }),
      {
        status: 500,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  }
});
```

### Paso 2: Modificar LeadCapture.tsx

Agrega la llamada a la Edge Function después de guardar en la base de datos:

```typescript
// En src/components/results/LeadCapture.tsx

const handleSubmit = async () => {
  // ... validaciones ...

  try {
    const results = calculateROI(quizData as QuizData);

    // 1. Guardar en base de datos
    const { error: dbError } = await supabase.from('quiz_leads').insert({
      // ... datos del lead
    });

    if (dbError) {
      throw dbError;
    }

    // 2. Enviar a SellerChat
    const functionUrl = `${import.meta.env.VITE_SUPABASE_URL}/functions/v1/sellerchat-webhook`;

    const sellerChatResponse = await fetch(functionUrl, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${import.meta.env.VITE_SUPABASE_ANON_KEY}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        phone_number: phone,
        country: quizData.country,
        currency: quizData.currencySymbol,
        payback_months: results.paybackMonths,
        monthly_savings: results.monthlySavings,
        annual_savings: results.annualSavings,
        efficiency_increase_percent: results.efficiencyIncreasePercent,
      }),
    });

    if (!sellerChatResponse.ok) {
      console.error('Error sending to SellerChat');
    }

    onSuccess();
  } catch (err) {
    setError('Hubo un error. Por favor intenta de nuevo.');
  } finally {
    setLoading(false);
  }
};
```

## Opción 2: Trigger Automático con Database Webhook

### Configurar en Supabase Dashboard

1. Ve a Database → Webhooks
2. Crea un nuevo webhook:
   - **Name**: `sellerchat_lead_webhook`
   - **Table**: `quiz_leads`
   - **Events**: `INSERT`
   - **URL**: `https://TU_PROYECTO.supabase.co/functions/v1/sellerchat-webhook`
   - **Headers**:
     ```
     Authorization: Bearer YOUR_SERVICE_ROLE_KEY
     Content-Type: application/json
     ```

3. La función se ejecutará automáticamente cada vez que se inserte un nuevo lead

## Opción 3: Integración Directa desde Frontend

Si SellerChat permite CORS, puedes enviar directamente desde el frontend:

```typescript
// En src/utils/sellerchat.ts

interface SellerChatConfig {
  apiKey: string;
  webhookUrl: string;
}

const config: SellerChatConfig = {
  apiKey: import.meta.env.VITE_SELLERCHAT_API_KEY,
  webhookUrl: import.meta.env.VITE_SELLERCHAT_WEBHOOK_URL,
};

export async function sendLeadToSellerChat(leadData: any) {
  try {
    const response = await fetch(config.webhookUrl, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${config.apiKey}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        phone: leadData.phone_number,
        name: `Lead Quiz - ${leadData.country}`,
        tags: ['quiz_roi', leadData.country.toLowerCase()],
        custom_fields: {
          payback_meses: leadData.payback_months,
          ahorro_mensual: leadData.monthly_savings,
          ahorro_anual: leadData.annual_savings,
        },
      }),
    });

    if (!response.ok) {
      throw new Error('Failed to send to SellerChat');
    }

    return await response.json();
  } catch (error) {
    console.error('SellerChat error:', error);
    throw error;
  }
}
```

Luego en `LeadCapture.tsx`:

```typescript
import { sendLeadToSellerChat } from '../../utils/sellerchat';

const handleSubmit = async () => {
  // ... guardar en DB ...

  try {
    await sendLeadToSellerChat({
      phone_number: phone,
      country: quizData.country,
      payback_months: results.paybackMonths,
      monthly_savings: results.monthlySavings,
      annual_savings: results.annualSavings,
    });
  } catch (error) {
    console.error('Error sending to SellerChat:', error);
  }

  onSuccess();
};
```

## Template de Mensaje para SellerChat

### Mensaje Inicial Automático

```
¡Hola! 👋

Gracias por completar nuestro análisis de ROI.

Tu diagnóstico está listo:

⏱️ Recuperación: 8.5 meses
💰 Ahorro mensual: $1,410
📈 Ahorro anual: $16,920

¿Te gustaría conocer más detalles sobre cómo implementar esta solución en tu negocio?

Opciones:
1️⃣ Ver más detalles
2️⃣ Agendar llamada con asesor
3️⃣ Solicitar cotización

Responde con el número de tu opción preferida.
```

### Respuestas Automatizadas

**Opción 1: Más detalles**
```
Perfecto! Te envío información detallada:

📊 ANÁLISIS COMPLETO:

Con tu producción actual de 1,000 empanadas/día:
• Costo manual por empanada: $0.50
• Costo con máquina: $0.08
• Ahorro por empanada: $0.42

🎯 BENEFICIOS:
✅ Reducción de costos en 84%
✅ Mayor consistencia en calidad
✅ Capacidad de escalar producción
✅ Menos dependencia de mano de obra

¿Qué te gustaría saber más?
- Opciones de financiamiento
- Garantía y soporte
- Casos de éxito
```

**Opción 2: Agendar llamada**
```
Excelente! Vamos a agendar una llamada.

¿Cuál horario te viene mejor?

📅 Mañana:
• 10:00 AM
• 2:00 PM
• 4:00 PM

📅 Pasado mañana:
• 10:00 AM
• 2:00 PM
• 4:00 PM

Responde con la fecha y hora que prefieras.
```

**Opción 3: Cotización**
```
¡Genial! Voy a preparar tu cotización personalizada.

Para darte la mejor opción, necesito confirmar:

1. ¿Tu producción es constante todo el año?
2. ¿Tienes instalación eléctrica trifásica?
3. ¿Prefieres pago de contado o financiamiento?

Te enviaré la cotización en menos de 24 horas. 📋
```

## Variables de Entorno Necesarias

```env
# SellerChat
VITE_SELLERCHAT_API_KEY=your_api_key
VITE_SELLERCHAT_WEBHOOK_URL=https://api.sellerchat.com/webhooks/leads

# O para Edge Functions:
SELLERCHAT_API_KEY=your_api_key
SELLERCHAT_WEBHOOK_URL=https://api.sellerchat.com/webhooks/leads
```

## Segmentación de Leads en SellerChat

### Por perfil de negocio:

**Tags sugeridos:**
- `quiz_roi` - Todos los leads del quiz
- `hot_lead` - Payback < 6 meses
- `warm_lead` - Payback 6-12 meses
- `cold_lead` - Payback > 12 meses
- País: `colombia`, `mexico`, `chile`, etc.

### Flujos automatizados:

**Para Hot Leads (Payback < 6 meses):**
```javascript
if (payback_months <= 6) {
  tags.push('hot_lead');
  priority = 'high';
  assignTo = 'senior_sales_rep';
  sendFollowUp = 'immediate'; // en 5 minutos
}
```

**Para Warm Leads (Payback 6-12 meses):**
```javascript
if (payback_months > 6 && payback_months <= 12) {
  tags.push('warm_lead');
  priority = 'medium';
  assignTo = 'sales_rep';
  sendFollowUp = 'same_day'; // en 2 horas
}
```

**Para Cold Leads (Payback > 12 meses):**
```javascript
if (payback_months > 12) {
  tags.push('cold_lead');
  priority = 'low';
  assignTo = 'nurture_sequence';
  sendFollowUp = 'next_day'; // al día siguiente
}
```

## Métricas y Reportes

Tracking de conversión en SellerChat:

```javascript
const metrics = {
  total_leads: 150,
  hot_leads: 45,
  warm_leads: 70,
  cold_leads: 35,
  conversion_rate: 28, // %
  avg_response_time: '3.5 minutes',
  avg_deal_size: 12500, // USD
};
```

## Testing

Para probar la integración sin enviar datos reales:

```typescript
if (Deno.env.get("ENVIRONMENT") === "development") {
  console.log("Would send to SellerChat:", leadData);
  return { success: true, dev_mode: true };
}
```
