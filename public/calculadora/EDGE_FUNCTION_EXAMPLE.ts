// Ejemplo completo de Edge Function para enviar reportes por WhatsApp
// Guarda este archivo y despliégalo usando el MCP tool de Supabase

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
  daily_savings: number;
  savings_per_unit: number;
  efficiency_increase_percent: number;
  manual_cost_per_unit: number;
  machine_cost_per_unit: number;
  manual_empanadas_per_hour: number;
  machine_empanadas_per_hour: number;
}

function formatCurrency(amount: number, currency: string): string {
  return `${currency}${amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
}

function getBusinessProfile(paybackMonths: number): string {
  if (paybackMonths <= 6) return "Startup";
  if (paybackMonths <= 12) return "Growth";
  return "Expanding";
}

function generateWhatsAppMessage(data: LeadData): string {
  const profile = getBusinessProfile(data.payback_months);

  let recommendation = "";
  if (profile === "Startup") {
    recommendation = "Excelente inversión. Tu tiempo de recuperación es muy corto, lo que indica que la máquina te permitirá escalar rápidamente.";
  } else if (profile === "Growth") {
    recommendation = "Buena inversión. Con un año de recuperación, esta máquina te ayudará a optimizar costos y aumentar tu capacidad productiva.";
  } else {
    recommendation = "Inversión de largo plazo. Asegura un flujo constante de producción y considera opciones de financiamiento.";
  }

  return `
🎯 *Tu Diagnóstico de ROI - Máquina de Empanadas*

Hola! Aquí está tu análisis completo:

━━━━━━━━━━━━━━━━━━━━
📍 *DATOS GENERALES*
━━━━━━━━━━━━━━━━━━━━
• País: ${data.country}
• Perfil: ${profile}

━━━━━━━━━━━━━━━━━━━━
⏱️ *RECUPERACIÓN DE INVERSIÓN*
━━━━━━━━━━━━━━━━━━━━
*${data.payback_months.toFixed(1)} meses*

Recuperarás tu inversión en menos de ${Math.ceil(data.payback_months)} meses

━━━━━━━━━━━━━━━━━━━━
💰 *AHORROS PROYECTADOS*
━━━━━━━━━━━━━━━━━━━━
• Ahorro diario: ${formatCurrency(data.daily_savings, data.currency)}
• Ahorro mensual: *${formatCurrency(data.monthly_savings, data.currency)}*
• Ahorro anual: *${formatCurrency(data.annual_savings, data.currency)}*

━━━━━━━━━━━━━━━━━━━━
📊 *COMPARACIÓN DE COSTOS*
━━━━━━━━━━━━━━━━━━━━
Por empanada:
• Manual: ${formatCurrency(data.manual_cost_per_unit, data.currency)}
• Con máquina: ${formatCurrency(data.machine_cost_per_unit, data.currency)}
• *Ahorro: ${formatCurrency(data.savings_per_unit, data.currency)}*

━━━━━━━━━━━━━━━━━━━━
⚡ *PRODUCTIVIDAD*
━━━━━━━━━━━━━━━━━━━━
• Manual: ${data.manual_empanadas_per_hour} emp/hora
• Con máquina: ${data.machine_empanadas_per_hour} emp/hora
• *Incremento: +${data.efficiency_increase_percent.toFixed(1)}%*

━━━━━━━━━━━━━━━━━━━━
💡 *RECOMENDACIÓN*
━━━━━━━━━━━━━━━━━━━━
${recommendation}

━━━━━━━━━━━━━━━━━━━━

¿Quieres más información o tienes alguna pregunta?

Responde a este mensaje y uno de nuestros asesores te contactará. 🚀

---
_Diagnóstico generado automáticamente_
`.trim();
}

async function sendWhatsAppBusinessAPI(to: string, message: string): Promise<boolean> {
  const token = Deno.env.get("WHATSAPP_TOKEN");
  const phoneNumberId = Deno.env.get("WHATSAPP_PHONE_NUMBER_ID");

  if (!token || !phoneNumberId) {
    console.error("WhatsApp credentials not configured");
    return false;
  }

  try {
    const response = await fetch(
      `https://graph.facebook.com/v17.0/${phoneNumberId}/messages`,
      {
        method: "POST",
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          messaging_product: "whatsapp",
          to: to,
          type: "text",
          text: {
            body: message,
          },
        }),
      }
    );

    if (!response.ok) {
      const error = await response.text();
      console.error("WhatsApp API error:", error);
      return false;
    }

    return true;
  } catch (error) {
    console.error("Error sending WhatsApp message:", error);
    return false;
  }
}

async function sendTwilioWhatsApp(to: string, message: string): Promise<boolean> {
  const accountSid = Deno.env.get("TWILIO_ACCOUNT_SID");
  const authToken = Deno.env.get("TWILIO_AUTH_TOKEN");
  const from = Deno.env.get("TWILIO_WHATSAPP_NUMBER");

  if (!accountSid || !authToken || !from) {
    console.error("Twilio credentials not configured");
    return false;
  }

  try {
    const url = `https://api.twilio.com/2010-04-01/Accounts/${accountSid}/Messages.json`;

    const body = new URLSearchParams({
      From: from,
      To: `whatsapp:${to}`,
      Body: message,
    });

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Authorization": `Basic ${btoa(`${accountSid}:${authToken}`)}`,
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: body.toString(),
    });

    if (!response.ok) {
      const error = await response.text();
      console.error("Twilio error:", error);
      return false;
    }

    return true;
  } catch (error) {
    console.error("Error sending Twilio message:", error);
    return false;
  }
}

Deno.serve(async (req: Request) => {
  if (req.method === "OPTIONS") {
    return new Response(null, { status: 200, headers: corsHeaders });
  }

  try {
    const leadData: LeadData = await req.json();

    const isDevelopment = Deno.env.get("ENVIRONMENT") === "development";

    const message = generateWhatsAppMessage(leadData);

    if (isDevelopment) {
      console.log("=== DEV MODE: WhatsApp Message ===");
      console.log("To:", leadData.phone_number);
      console.log("Message:", message);
      console.log("=================================");

      return new Response(
        JSON.stringify({
          success: true,
          dev_mode: true,
          message: "Message logged in development mode",
        }),
        {
          headers: { ...corsHeaders, "Content-Type": "application/json" },
        }
      );
    }

    const provider = Deno.env.get("WHATSAPP_PROVIDER") || "whatsapp_business";
    let success = false;

    if (provider === "whatsapp_business") {
      success = await sendWhatsAppBusinessAPI(leadData.phone_number, message);
    } else if (provider === "twilio") {
      success = await sendTwilioWhatsApp(leadData.phone_number, message);
    } else {
      throw new Error(`Unknown provider: ${provider}`);
    }

    if (!success) {
      throw new Error("Failed to send message");
    }

    console.log(`✅ Report sent successfully to ${leadData.phone_number}`);

    return new Response(
      JSON.stringify({
        success: true,
        message: "Report sent successfully",
        to: leadData.phone_number,
      }),
      {
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  } catch (error) {
    console.error("Error in send-quiz-report function:", error);

    return new Response(
      JSON.stringify({
        success: false,
        error: error.message || "Unknown error",
      }),
      {
        status: 500,
        headers: { ...corsHeaders, "Content-Type": "application/json" },
      }
    );
  }
});

// Para desplegar esta función, usa:
// mcp__supabase__deploy_edge_function con:
// - name: "send-quiz-report"
// - slug: "send-quiz-report"
// - verify_jwt: false (si quieres que sea público) o true (si quieres autenticación)
// - files: [{ name: "index.ts", content: "<contenido de este archivo>" }]
