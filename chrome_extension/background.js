const DEFAULT_SETTINGS = {
  endpoint: "https://arichat.co/api/whatsapp/outgoing",
  phone: "573206945548",
  APIKEY: "II([:{~Lm}+FXA}$Hmc+90`ZBVca[Wo42}a.(bg1sX!Oo5)X",
  crm_user_id: ""
};

const SETTINGS_KEYS = ["endpoint", "phone", "APIKEY", "crm_user_id"];
const INSTANCE_SETTINGS_KEY = "wa_crm_instance_settings";
const LAST_INSTANCE_KEY = "wa_crm_last_instance_key";
const DEFAULT_INSTANCE_KEY = "__default__";
const LOG_PREFIX = "[whatsapp-crm-extension]";

const maskApiKey = (value) => {
  const text = String(value || "").trim();
  if (!text) {
    return "";
  }

  const prefix = text.slice(0, 6);
  return `${prefix}... (len:${text.length})`;
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
  await chrome.storage.local.set({
    last_runtime_status: {
      ...status,
      at: new Date().toISOString()
    }
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

  const messageInstanceKey = String(message.payload?.instance_key || "").trim();
  const { settings, key: resolvedKey } = await getSettingsForInstance(messageInstanceKey);
  const { endpoint, phone: fallbackPhone, APIKEY, crm_user_id } = settings;
  const resolvedEndpoint = String(endpoint || DEFAULT_SETTINGS.endpoint).trim();

  await setActiveInstanceKey(resolvedKey);

  console.info(`${LOG_PREFIX} Config activa para envio`, {
    instanceKey: resolvedKey,
    endpoint: resolvedEndpoint,
    phoneFallback: fallbackPhone,
    apiKey: maskApiKey(APIKEY)
  });

  if (!APIKEY) {
    console.warn(`${LOG_PREFIX} Config incompleta: falta APIKEY en storage.`);
    notifyTab(sender, "error", "Extension WhatsApp CRM sin APIKEY configurado.");
    await setRuntimeStatus({
      type: "error",
      reason: "missing_apikey",
      instanceKey: resolvedKey
    });
    return;
  }

  if (!/^https?:\/\//i.test(resolvedEndpoint)) {
    console.warn(`${LOG_PREFIX} Endpoint invalido:`, resolvedEndpoint);
    notifyTab(sender, "error", "Endpoint invalido en configuracion de extension.");
    await setRuntimeStatus({
      type: "error",
      reason: "invalid_endpoint",
      endpoint: resolvedEndpoint,
      instanceKey: resolvedKey
    });
    return;
  }

  const targetPhone = (message.payload?.phone || fallbackPhone || "").trim();
  if (!targetPhone) {
    console.warn(`${LOG_PREFIX} No se pudo resolver phone destino para el mensaje.`);
    notifyTab(sender, "error", "No se pudo identificar el teléfono destino.");
    await setRuntimeStatus({
      type: "error",
      reason: "missing_phone",
      messageId: message.payload?.id || null,
      instanceKey: resolvedKey
    });
    return;
  }

  const payload = {
    id: message.payload?.id ?? `wa_crm_${Date.now()}`,
    type: message.payload?.type ?? "chat",
    user: message.payload?.user ?? "wa_crm_extension",
    instance_key: resolvedKey,
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
      phone: payload.phone,
      endpoint: resolvedEndpoint,
      instanceKey: resolvedKey
    });

    await setRuntimeStatus({
      type: "captured",
      messageId: payload.id,
      phone: payload.phone,
      endpoint: resolvedEndpoint,
      instanceKey: resolvedKey
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
        instanceKey: resolvedKey
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
      instanceKey: resolvedKey
    });

    notifyTab(sender, "info", `Mensaje encolado (${payload.id.slice(0, 12)}...)`);
  } catch (error) {
    console.error(`${LOG_PREFIX} Error enviando webhook:`, error);
    notifyTab(sender, "error", "No se pudo conectar con el webhook WhatsApp.");
    await setRuntimeStatus({
      type: "error",
      reason: "network_error",
      messageId: payload.id,
      instanceKey: resolvedKey
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

  await chrome.storage.local.set(payload);
  await chrome.storage.sync.set(payload);
});
