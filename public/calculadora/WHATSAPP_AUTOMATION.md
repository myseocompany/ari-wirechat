# Automatización de WhatsApp

## Envío Automático de Diagnóstico por WhatsApp

### Opción 1: Edge Function de Supabase (Recomendado)

Crea una Edge Function que se active automáticamente cuando se inserta un nuevo lead.

#### Paso 1: Crear la Edge Function

Puedes usar el tool `mcp__supabase__deploy_edge_function` para crear una función llamada `send-quiz-report`.

Código de ejemplo:

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
  payback_months: number;
  monthly_savings: number;
  annual_savings: number;
  efficiency_increase_percent: number;
  currency: string;
}

Deno.serve(async (req: Request) => {
  if (req.method === "OPTIONS") {
    return new Response(null, { status: 200, headers: corsHeaders });
  }

  try {
    const leadData: LeadData = await req.json();

    const message = `
🎯 *Tu Diagnóstico de ROI - Máquina de Empanadas*

⏱️ *Tiempo de Recuperación:* ${leadData.payback_months.toFixed(1)} meses

💰 *Ahorro Mensual:* ${leadData.currency}${leadData.monthly_savings.toFixed(2)}

📈 *Ahorro Anual:* ${leadData.currency}${leadData.annual_savings.toFixed(2)}

⚡ *Incremento de Eficiencia:* +${leadData.efficiency_increase_percent.toFixed(1)}%

---

¿Quieres conocer más detalles sobre cómo maximizar tu inversión?

Responde a este mensaje y te asesoramos personalmente. 🚀
    `.trim();

    // Aquí integrarías con tu proveedor de WhatsApp
    // Ejemplo con WhatsApp Business API:
    const whatsappResponse = await fetch(
      `https://graph.facebook.com/v17.0/YOUR_PHONE_NUMBER_ID/messages`,
      {
        method: "POST",
        headers: {
          Authorization: `Bearer ${Deno.env.get("WHATSAPP_TOKEN")}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          messaging_product: "whatsapp",
          to: leadData.phone_number,
          type: "text",
          text: { body: message },
        }),
      }
    );

    if (!whatsappResponse.ok) {
      throw new Error("Failed to send WhatsApp message");
    }

    return new Response(
      JSON.stringify({ success: true, message: "Report sent successfully" }),
      {
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  } catch (error) {
    return new Response(
      JSON.stringify({ success: false, error: error.message }),
      {
        status: 500,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  }
});
```

#### Paso 2: Configurar Database Webhook

En tu panel de Supabase:
1. Ve a Database → Webhooks
2. Crea un nuevo webhook que se active en `INSERT` en la tabla `quiz_leads`
3. URL: `https://YOUR_PROJECT.supabase.co/functions/v1/send-quiz-report`
4. Headers: Añade el header de autenticación

#### Paso 3: Llamar desde el frontend

Modifica `LeadCapture.tsx` para llamar a la Edge Function después de guardar:

```typescript
// Después de insertar en la base de datos:
const { error: dbError } = await supabase.from('quiz_leads').insert({...});

if (!dbError) {
  // Llamar a la Edge Function
  const functionUrl = `${import.meta.env.VITE_SUPABASE_URL}/functions/v1/send-quiz-report`;

  await fetch(functionUrl, {
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
}
```

### Opción 2: Integración con Twilio

Si prefieres usar Twilio para enviar mensajes por WhatsApp:

```typescript
// Edge Function alternativa con Twilio
const sendWithTwilio = async (to: string, message: string) => {
  const accountSid = Deno.env.get("TWILIO_ACCOUNT_SID");
  const authToken = Deno.env.get("TWILIO_AUTH_TOKEN");
  const from = Deno.env.get("TWILIO_WHATSAPP_NUMBER"); // ej: whatsapp:+14155238886

  const url = `https://api.twilio.com/2010-04-01/Accounts/${accountSid}/Messages.json`;

  const body = new URLSearchParams({
    From: from,
    To: `whatsapp:${to}`,
    Body: message,
  });

  const response = await fetch(url, {
    method: "POST",
    headers: {
      Authorization: `Basic ${btoa(`${accountSid}:${authToken}`)}`,
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: body.toString(),
  });

  return response.json();
};
```

### Opción 3: URL de WhatsApp Direct

La forma más simple (sin API) es generar un link de WhatsApp:

```typescript
const generateWhatsAppLink = (phone: string, message: string) => {
  const encodedMessage = encodeURIComponent(message);
  return `https://wa.me/${phone}?text=${encodedMessage}`;
};

// En FullReport.tsx, añadir botón:
<a
  href={generateWhatsAppLink(phoneNumber, reportMessage)}
  target="_blank"
  rel="noopener noreferrer"
  className="flex items-center justify-center gap-2 px-6 py-3 bg-green-500 text-white rounded-lg"
>
  <Phone className="w-5 h-5" />
  Recibir por WhatsApp
</a>
```

### Plantillas de Mensaje Mejoradas

#### Mensaje Corto (SMS):
```
🎯 ROI Empanadas
⏱️ Recuperación: 8.5 meses
💰 Ahorro anual: $15,000
📊 Ver reporte completo: [LINK]
```

#### Mensaje Completo (WhatsApp):
```
¡Hola! 👋

Aquí está tu *Diagnóstico de ROI* para tu máquina de empanadas:

📍 *País:* Colombia
💵 *Inversión:* $12,000

✅ *RESULTADOS:*
⏱️ Recuperación: *8.5 meses*
💰 Ahorro mensual: *$1,410*
📈 Ahorro anual: *$16,920*
⚡ Eficiencia: *+1400%*

🎯 *PERFIL:* Growth
Tu inversión se recuperará en menos de un año, ideal para escalar tu negocio.

💡 *PRÓXIMOS PASOS:*
1. Analiza opciones de financiamiento
2. Planifica tu flujo de caja
3. Contacta a tu proveedor

¿Necesitas asesoría personalizada?
Responde este mensaje y te ayudamos. 🚀
```

### Variables de Entorno Necesarias

```env
# Para WhatsApp Business API
WHATSAPP_TOKEN=your_whatsapp_business_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id

# Para Twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_WHATSAPP_NUMBER=whatsapp:+14155238886
```

### Testing

Para probar el envío sin consumir créditos:

```typescript
const isDevelopment = Deno.env.get("ENVIRONMENT") === "development";

if (isDevelopment) {
  console.log("WhatsApp message (dev mode):", message);
  return new Response(
    JSON.stringify({ success: true, dev_mode: true }),
    { headers: corsHeaders }
  );
}
```
