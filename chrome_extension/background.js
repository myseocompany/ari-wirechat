const DEFAULT_SETTINGS = {
  endpoint: "https://arichat.co/api/whatsapp/outgoing",
  phone: "573004410097",
  APIKEY: "II([:{~Lm}+FXA}$Hmc+90`ZBVca[Wo42}a.(bg1sX!Oo5)X",
  crm_user_id: "",
  crm_customer_id: ""
};

const SETTINGS_KEYS = ["endpoint", "phone", "APIKEY", "crm_user_id", "crm_customer_id"];
const INSTANCE_SETTINGS_KEY = "wa_crm_instance_settings";
const LAST_INSTANCE_KEY = "wa_crm_last_instance_key";
const DEFAULT_INSTANCE_KEY = "__default__";
const RUNTIME_HISTORY_KEY = "wa_crm_runtime_history";
const RUNTIME_HISTORY_MAX = 250;
const OUTGOING_MESSAGES_KEY = "wa_crm_outgoing_messages";
const OUTGOING_MESSAGES_MAX = 500;
const LOG_PREFIX = "[whatsapp-crm-extension]";
const MONITOR_WINDOW_URL = chrome.runtime.getURL("monitor.html");

let monitorWindowId = null;

const maskApiKey = (value) => {
  const text = String(value || "").trim();
  if (!text) {
    return "";
  }

  const prefix = text.slice(0, 6);
  return `${prefix}... (len:${text.length})`;
};

const normalizeContent = (value) => {
  return String(value || "")
    .replace(/\s+/g, " ")
    .trim()
    .slice(0, 4000);
};

const normalizeSettings = (settings) => {
  const normalized = {
    ...settings
  };

  if (!String(normalized.endpoint || "").trim()) {
    normalized.endpoint = DEFAULT_SETTINGS.endpoint;
  }

  if (!String(normalized.APIKEY || "").trim()) {
    normalized.APIKEY = DEFAULT_SETTINGS.APIKEY;
  }

  if (!String(normalized.phone || "").trim()) {
    normalized.phone = DEFAULT_SETTINGS.phone;
  }

  normalized.crm_user_id = String(normalized.crm_user_id || "").trim();
  normalized.crm_customer_id = String(normalized.crm_customer_id || "").trim();

  return normalized;
};

const pickLegacySettings = (data) => {
  return SETTINGS_KEYS.reduce((carry, key) => {
    if (Object.prototype.hasOwnProperty.call(data || {}, key)) {
      carry[key] = data[key];
    }

    return carry;
  }, {});
};

const mergeInstanceMap = (localMap, syncMap) => {
  return {
    ...(syncMap && typeof syncMap === "object" ? syncMap : {}),
    ...(localMap && typeof localMap === "object" ? localMap : {})
  };
};

const getStorageBundle = async () => {
  const keys = [INSTANCE_SETTINGS_KEY, LAST_INSTANCE_KEY, ...SETTINGS_KEYS];
  const [fromLocal, fromSync] = await Promise.all([
    chrome.storage.local.get(keys),
    chrome.storage.sync.get(keys)
  ]);

  return { fromLocal, fromSync };
};

const getSettingsForInstance = async (instanceKey = "") => {
  const { fromLocal, fromSync } = await getStorageBundle();

  const mergedMap = mergeInstanceMap(fromLocal[INSTANCE_SETTINGS_KEY], fromSync[INSTANCE_SETTINGS_KEY]);
  const fallbackSettings = normalizeSettings({
    ...DEFAULT_SETTINGS,
    ...pickLegacySettings(fromSync),
    ...pickLegacySettings(fromLocal)
  });

  const resolvedKey = String(
    instanceKey || fromLocal[LAST_INSTANCE_KEY] || fromSync[LAST_INSTANCE_KEY] || DEFAULT_INSTANCE_KEY
  ).trim() || DEFAULT_INSTANCE_KEY;

  const scopedSettings = mergedMap[resolvedKey]
    ? normalizeSettings(mergedMap[resolvedKey])
    : fallbackSettings;

  return {
    key: resolvedKey,
    settings: scopedSettings,
    map: mergedMap
  };
};

const setActiveInstanceKey = async (instanceKey) => {
  const key = String(instanceKey || "").trim();
  if (!key) {
    return;
  }

  const payload = {
    [LAST_INSTANCE_KEY]: key
  };

  await chrome.storage.local.set(payload);
  await chrome.storage.sync.set(payload);
};

const setRuntimeStatus = async (status) => {
  const nextStatus = {
    ...status,
    at: new Date().toISOString()
  };
  const existing = await chrome.storage.local.get([RUNTIME_HISTORY_KEY]);
  const currentHistory = Array.isArray(existing[RUNTIME_HISTORY_KEY]) ? existing[RUNTIME_HISTORY_KEY] : [];
  const updatedHistory = [...currentHistory, nextStatus].slice(-RUNTIME_HISTORY_MAX);

  await chrome.storage.local.set({
    last_runtime_status: nextStatus,
    [RUNTIME_HISTORY_KEY]: updatedHistory
  });
};

const upsertOutgoingMessage = async (entry) => {
  const messageId = String(entry?.id || "").trim();
  if (!messageId) {
    return;
  }

  const existing = await chrome.storage.local.get([OUTGOING_MESSAGES_KEY]);
  const current = Array.isArray(existing[OUTGOING_MESSAGES_KEY]) ? existing[OUTGOING_MESSAGES_KEY] : [];
  const now = new Date().toISOString();
  const nextEntryBase = {
    id: messageId,
    type: String(entry?.type || "chat").trim() || "chat",
    instance_key: String(entry?.instance_key || "").trim() || null,
    phone: String(entry?.phone || "").trim(),
    state: String(entry?.state || "captured").trim() || "captured",
    reason: entry?.reason ? String(entry.reason) : null,
    endpoint: entry?.endpoint ? String(entry.endpoint) : null,
    status: typeof entry?.status === "number" ? entry.status : entry?.status ?? null,
    updated_at: now
  };

  const normalizedContent = normalizeContent(entry?.content || "");
  if (normalizedContent) {
    nextEntryBase.content = normalizedContent;
  }

  const index = current.findIndex((item) => String(item?.id || "").trim() === messageId);
  if (index >= 0) {
    const previous = current[index] || {};
    current[index] = {
      ...previous,
      ...nextEntryBase,
      content: nextEntryBase.content || previous.content || "",
      created_at: previous.created_at || now
    };
  } else {
    current.unshift({
      ...nextEntryBase,
      content: nextEntryBase.content || "",
      created_at: now
    });
  }

  current.sort((a, b) => String(b.updated_at || "").localeCompare(String(a.updated_at || "")));
  const clipped = current.slice(0, OUTGOING_MESSAGES_MAX);

  await chrome.storage.local.set({
    [OUTGOING_MESSAGES_KEY]: clipped
  });
};

const notifyTab = (sender, level, text) => {
  const tabId = sender?.tab?.id;
  if (typeof tabId !== "number") {
    return;
  }

  chrome.tabs.sendMessage(tabId, {
    type: "WA_CRM_STATUS",
    payload: { level, text }
  });
};

chrome.runtime.onMessage.addListener(async (message, sender) => {
  if (message?.type === "WA_CRM_RUNTIME") {
    const runtimeInstanceKey = String(message.payload?.detail?.instance_key || "").trim();
    if (runtimeInstanceKey) {
      await setActiveInstanceKey(runtimeInstanceKey);
    }

    await setRuntimeStatus({
      type: "runtime",
      reason: String(message.payload?.reason || "content_event"),
      detail: message.payload?.detail || null,
      instanceKey: runtimeInstanceKey || null
    });
    return;
  }

  if (message?.type !== "OUTGOING_MESSAGE") {
    return;
  }

  const rawMessageId = String(message.payload?.id || "").trim() || `wa_crm_${Date.now()}`;
  const rawType = String(message.payload?.type || "chat").trim() || "chat";
  const rawPhone = String(message.payload?.phone || "").trim();
  const rawContent = normalizeContent(message.payload?.content || "");
  const messageInstanceKey = String(message.payload?.instance_key || "").trim();
  const { settings, key: resolvedKey } = await getSettingsForInstance(messageInstanceKey);
  const { endpoint, phone: fallbackPhone, APIKEY, crm_user_id, crm_customer_id } = settings;
  const resolvedEndpoint = String(endpoint || DEFAULT_SETTINGS.endpoint).trim();

  await setActiveInstanceKey(resolvedKey);

  console.info(`${LOG_PREFIX} Config activa para envio`, {
    instanceKey: resolvedKey,
    endpoint: resolvedEndpoint,
    phoneFallback: fallbackPhone,
    apiKey: maskApiKey(APIKEY),
    crm_customer_id: /^\d+$/.test(String(crm_customer_id || "").trim()) ? Number(crm_customer_id) : null
  });

  if (!APIKEY) {
    console.warn(`${LOG_PREFIX} Config incompleta: falta APIKEY en storage.`);
    notifyTab(sender, "error", "Extension WhatsApp CRM sin APIKEY configurado.");
    await upsertOutgoingMessage({
      id: rawMessageId,
      type: rawType,
      instance_key: resolvedKey,
      phone: rawPhone || String(fallbackPhone || "").trim(),
      content: rawContent,
      endpoint: resolvedEndpoint,
      state: "error",
      reason: "missing_apikey"
    });
    await setRuntimeStatus({
      type: "error",
      reason: "missing_apikey",
      messageId: rawMessageId,
      phone: rawPhone || String(fallbackPhone || "").trim(),
      content_preview: rawContent.slice(0, 140),
      instanceKey: resolvedKey
    });
    return;
  }

  if (!/^https?:\/\//i.test(resolvedEndpoint)) {
    console.warn(`${LOG_PREFIX} Endpoint invalido:`, resolvedEndpoint);
    notifyTab(sender, "error", "Endpoint invalido en configuracion de extension.");
    await upsertOutgoingMessage({
      id: rawMessageId,
      type: rawType,
      instance_key: resolvedKey,
      phone: rawPhone || String(fallbackPhone || "").trim(),
      content: rawContent,
      endpoint: resolvedEndpoint,
      state: "error",
      reason: "invalid_endpoint"
    });
    await setRuntimeStatus({
      type: "error",
      reason: "invalid_endpoint",
      endpoint: resolvedEndpoint,
      messageId: rawMessageId,
      phone: rawPhone || String(fallbackPhone || "").trim(),
      content_preview: rawContent.slice(0, 140),
      instanceKey: resolvedKey
    });
    return;
  }

  const targetPhone = (message.payload?.phone || fallbackPhone || "").trim();
  if (!targetPhone) {
    console.warn(`${LOG_PREFIX} No se pudo resolver phone destino para el mensaje.`);
    notifyTab(sender, "error", "No se pudo identificar el teléfono destino.");
    await upsertOutgoingMessage({
      id: rawMessageId,
      type: rawType,
      instance_key: resolvedKey,
      content: rawContent,
      endpoint: resolvedEndpoint,
      state: "error",
      reason: "missing_phone"
    });
    await setRuntimeStatus({
      type: "error",
      reason: "missing_phone",
      messageId: rawMessageId,
      content_preview: rawContent.slice(0, 140),
      instanceKey: resolvedKey
    });
    return;
  }

  const payload = {
    id: rawMessageId,
    type: rawType,
    user: message.payload?.user ?? "wa_crm_extension",
    instance_key: resolvedKey,
    phone: targetPhone,
    content: rawContent,
    APIKEY
  };

  if (/^\d+$/.test(String(crm_user_id || "").trim())) {
    payload.crm_user_id = Number(crm_user_id);
  }

  if (/^\d+$/.test(String(crm_customer_id || "").trim())) {
    payload.crm_customer_id = Number(crm_customer_id);
  }

  try {
    console.info(`${LOG_PREFIX} Enviando mensaje`, {
      id: payload.id,
      phone: payload.phone,
      endpoint: resolvedEndpoint,
      instanceKey: resolvedKey
    });

    await setRuntimeStatus({
      type: "captured",
      messageId: payload.id,
      phone: payload.phone,
      endpoint: resolvedEndpoint,
      content_preview: payload.content.slice(0, 140),
      instanceKey: resolvedKey
    });
    await upsertOutgoingMessage({
      id: payload.id,
      type: payload.type,
      instance_key: resolvedKey,
      phone: payload.phone,
      content: payload.content,
      endpoint: resolvedEndpoint,
      state: "captured"
    });

    const response = await fetch(resolvedEndpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorBody = await response.text();
      console.error(`${LOG_PREFIX} Webhook WhatsApp CRM respondio con error:`, response.status, errorBody);
      notifyTab(sender, "error", `Error ${response.status} al encolar mensaje WhatsApp.`);
      await setRuntimeStatus({
        type: "error",
        reason: "http_error",
        status: response.status,
        messageId: payload.id,
        phone: payload.phone,
        content_preview: payload.content.slice(0, 140),
        instanceKey: resolvedKey
      });
      await upsertOutgoingMessage({
        id: payload.id,
        instance_key: resolvedKey,
        phone: payload.phone,
        content: payload.content,
        endpoint: resolvedEndpoint,
        state: "error",
        reason: "http_error",
        status: response.status
      });
      return;
    }

    console.info(`${LOG_PREFIX} Mensaje encolado correctamente`, {
      id: payload.id,
      status: response.status,
      instanceKey: resolvedKey
    });

    await setRuntimeStatus({
      type: "queued",
      messageId: payload.id,
      status: response.status,
      phone: payload.phone,
      content_preview: payload.content.slice(0, 140),
      instanceKey: resolvedKey
    });
    await upsertOutgoingMessage({
      id: payload.id,
      instance_key: resolvedKey,
      phone: payload.phone,
      content: payload.content,
      endpoint: resolvedEndpoint,
      state: "queued",
      status: response.status
    });

    notifyTab(sender, "info", `Mensaje encolado (${payload.id.slice(0, 12)}...)`);
  } catch (error) {
    console.error(`${LOG_PREFIX} Error enviando webhook:`, error);
    notifyTab(sender, "error", "No se pudo conectar con el webhook WhatsApp.");
    await setRuntimeStatus({
      type: "error",
      reason: "network_error",
      messageId: payload.id,
      phone: payload.phone,
      content_preview: payload.content.slice(0, 140),
      instanceKey: resolvedKey
    });
    await upsertOutgoingMessage({
      id: payload.id,
      instance_key: resolvedKey,
      phone: payload.phone,
      content: payload.content,
      endpoint: resolvedEndpoint,
      state: "error",
      reason: "network_error"
    });
  }
});

chrome.runtime.onInstalled.addListener(async () => {
  const initial = await getSettingsForInstance();
  const mergedMap = {
    ...initial.map,
    [initial.key]: initial.settings
  };

  const payload = {
    [INSTANCE_SETTINGS_KEY]: mergedMap,
    [LAST_INSTANCE_KEY]: initial.key,
    ...initial.settings
  };

  const existingOutgoing = await chrome.storage.local.get([OUTGOING_MESSAGES_KEY]);
  if (!Array.isArray(existingOutgoing[OUTGOING_MESSAGES_KEY])) {
    payload[OUTGOING_MESSAGES_KEY] = [];
  }

  await chrome.storage.local.set(payload);
  await chrome.storage.sync.set(payload);
});

const openMonitorWindow = async () => {
  if (typeof monitorWindowId === "number") {
    try {
      const existingWindow = await chrome.windows.get(monitorWindowId);
      if (existingWindow && existingWindow.id) {
        await chrome.windows.update(existingWindow.id, {
          focused: true
        });
        return;
      }
    } catch (error) {
      monitorWindowId = null;
    }
  }

  const created = await chrome.windows.create({
    url: MONITOR_WINDOW_URL,
    type: "popup",
    width: 460,
    height: 780,
    focused: true
  });

  monitorWindowId = created?.id ?? null;
};

chrome.action.onClicked.addListener(() => {
  openMonitorWindow().catch((error) => {
    console.error(`${LOG_PREFIX} No se pudo abrir monitor`, error);
  });
});

chrome.windows.onRemoved.addListener((windowId) => {
  if (windowId === monitorWindowId) {
    monitorWindowId = null;
  }
});
