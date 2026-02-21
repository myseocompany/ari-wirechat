const ENDPOINT = "https://arichat.co/api/sellerchat/outgoing";

chrome.runtime.onMessage.addListener(async (message, sender) => {
  if (message?.type !== "OUTGOING_MESSAGE") return;

  const { phone: fallbackPhone, APIKEY, crm_user_id } = await chrome.storage.sync.get([
    "phone",
    "APIKEY",
    "crm_user_id"
  ]);

  if (!APIKEY) {
    console.warn("Config incompleta: falta APIKEY en storage.");
    return;
  }

  const targetPhone = (message.payload?.phone || fallbackPhone || "").trim();
  if (!targetPhone) {
    console.warn("No se pudo resolver phone destino para el mensaje.");
    return;
  }

  const payload = {
    id: message.payload?.id ?? `sellerchat_${Date.now()}`,
    type: message.payload?.type ?? "chat",
    user: message.payload?.user ?? "sellerchat_extension",
    phone: targetPhone,
    content: message.payload?.content ?? "",
    APIKEY
  };

  if (/^\d+$/.test(String(crm_user_id || "").trim())) {
    payload.crm_user_id = Number(crm_user_id);
  }

  try {
    const response = await fetch(ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorBody = await response.text();
      console.error("Webhook sellerChat respondió con error:", response.status, errorBody);
    }
  } catch (err) {
    console.error("Error enviando webhook:", err);
  }
});
