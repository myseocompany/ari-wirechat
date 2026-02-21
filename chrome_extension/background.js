const ENDPOINT = "https://arichat.co/api/sellerchat/outgoing";
const LOG_PREFIX = "[sellerchat-extension]";

const notifyTab = (sender, level, text) => {
  const tabId = sender?.tab?.id;
  if (typeof tabId !== "number") {
    return;
  }

  chrome.tabs.sendMessage(tabId, {
    type: "SELLERCHAT_STATUS",
    payload: { level, text }
  });
};

chrome.runtime.onMessage.addListener(async (message, sender) => {
  if (message?.type !== "OUTGOING_MESSAGE") return;

  const { phone: fallbackPhone, APIKEY, crm_user_id } = await chrome.storage.sync.get([
    "phone",
    "APIKEY",
    "crm_user_id"
  ]);

  if (!APIKEY) {
    console.warn(`${LOG_PREFIX} Config incompleta: falta APIKEY en storage.`);
    notifyTab(sender, "error", "Extensión sellerChat sin APIKEY configurado.");
    return;
  }

  const targetPhone = (message.payload?.phone || fallbackPhone || "").trim();
  if (!targetPhone) {
    console.warn(`${LOG_PREFIX} No se pudo resolver phone destino para el mensaje.`);
    notifyTab(sender, "error", "No se pudo identificar el teléfono destino.");
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
    console.info(`${LOG_PREFIX} Enviando mensaje`, {
      id: payload.id,
      phone: payload.phone
    });

    const response = await fetch(ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorBody = await response.text();
      console.error(`${LOG_PREFIX} Webhook sellerChat respondió con error:`, response.status, errorBody);
      notifyTab(sender, "error", `Error ${response.status} al encolar mensaje sellerChat.`);
      return;
    }

    console.info(`${LOG_PREFIX} Mensaje encolado correctamente`, {
      id: payload.id,
      status: response.status
    });
    notifyTab(sender, "info", `Mensaje encolado (${payload.id.slice(0, 12)}...)`);
  } catch (err) {
    console.error(`${LOG_PREFIX} Error enviando webhook:`, err);
    notifyTab(sender, "error", "No se pudo conectar con el webhook sellerChat.");
  }
});
