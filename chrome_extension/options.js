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
const LOG_PREFIX = "[wa-crm-popup]";

let activeInstanceKey = DEFAULT_INSTANCE_KEY;

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
  const storageKeys = [INSTANCE_SETTINGS_KEY, LAST_INSTANCE_KEY, ...SETTINGS_KEYS];
  const [fromLocal, fromSync] = await Promise.all([
    chrome.storage.local.get(storageKeys),
    chrome.storage.sync.get(storageKeys)
  ]);

  return { fromLocal, fromSync };
};

const getSettingsForInstance = async (instanceKey = "") => {
  const { fromLocal, fromSync } = await getStorageBundle();

  const mergedMap = mergeInstanceMap(fromLocal[INSTANCE_SETTINGS_KEY], fromSync[INSTANCE_SETTINGS_KEY]);
  const mergedLegacy = normalizeSettings({
    ...DEFAULT_SETTINGS,
    ...pickLegacySettings(fromSync),
    ...pickLegacySettings(fromLocal)
  });

  const resolvedKey = String(
    instanceKey || fromLocal[LAST_INSTANCE_KEY] || fromSync[LAST_INSTANCE_KEY] || DEFAULT_INSTANCE_KEY
  ).trim() || DEFAULT_INSTANCE_KEY;

  const scopedSettings = mergedMap[resolvedKey]
    ? normalizeSettings(mergedMap[resolvedKey])
    : mergedLegacy;

  return {
    key: resolvedKey,
    settings: scopedSettings,
    map: mergedMap
  };
};

const saveSettingsForInstance = async (instanceKey, settings) => {
  const key = String(instanceKey || DEFAULT_INSTANCE_KEY).trim() || DEFAULT_INSTANCE_KEY;
  const normalized = normalizeSettings(settings);
  const { map } = await getSettingsForInstance(key);
  const mergedMap = {
    ...map,
    [key]: normalized
  };

  const payload = {
    [INSTANCE_SETTINGS_KEY]: mergedMap,
    [LAST_INSTANCE_KEY]: key,
    ...normalized
  };

  await chrome.storage.local.set(payload);
  await chrome.storage.sync.set(payload);

  return {
    key,
    settings: normalized
  };
};

const renderRuntime = async (runtimeElement) => {
  const { last_runtime_status } = await chrome.storage.local.get(["last_runtime_status"]);
  if (!last_runtime_status) {
    runtimeElement.textContent = "Sin actividad registrada.";
    return;
  }

  runtimeElement.textContent = [
    `Estado: ${last_runtime_status.type || "unknown"}`,
    `Motivo: ${last_runtime_status.reason || "-"}`,
    `Instancia: ${last_runtime_status.instanceKey || "-"}`,
    `Mensaje: ${last_runtime_status.messageId || "-"}`,
    `Phone: ${last_runtime_status.phone || "-"}`,
    `HTTP: ${last_runtime_status.status || "-"}`,
    `Endpoint: ${last_runtime_status.endpoint || "-"}`,
    `Detail: ${last_runtime_status.detail ? JSON.stringify(last_runtime_status.detail) : "-"}`,
    `Hora: ${last_runtime_status.at || "-"}`
  ].join("\n");
};

const renderSettings = (settings, key, elements) => {
  elements.instanceInput.value = key;
  elements.endpointInput.value = settings.endpoint;
  elements.phoneInput.value = settings.phone;
  elements.apiInput.value = settings.APIKEY;
  elements.crmUserInput.value = settings.crm_user_id;
};

document.addEventListener("DOMContentLoaded", async () => {
  const elements = {
    instanceInput: document.getElementById("instance_key"),
    endpointInput: document.getElementById("endpoint"),
    phoneInput: document.getElementById("phone"),
    apiInput: document.getElementById("APIKEY"),
    crmUserInput: document.getElementById("crm_user_id"),
    status: document.getElementById("status"),
    runtime: document.getElementById("runtime")
  };

  const initial = await getSettingsForInstance();
  activeInstanceKey = initial.key;
  renderSettings(initial.settings, initial.key, elements);
  await saveSettingsForInstance(activeInstanceKey, initial.settings);
  await renderRuntime(elements.runtime);

  console.info(`${LOG_PREFIX} Config cargada`, {
    instanceKey: activeInstanceKey,
    endpoint: initial.settings.endpoint,
    phone: initial.settings.phone,
    apiKey: maskApiKey(initial.settings.APIKEY),
    crm_user_id: initial.settings.crm_user_id || null
  });

  document.getElementById("save").onclick = async () => {
    const endpointValue = elements.endpointInput.value.trim();
    if (!/^https?:\/\//i.test(endpointValue)) {
      elements.status.textContent = "Endpoint invalido. Debe iniciar con http:// o https://";
      return;
    }

    const toSave = {
      endpoint: endpointValue,
      phone: elements.phoneInput.value.trim(),
      APIKEY: elements.apiInput.value.trim(),
      crm_user_id: elements.crmUserInput.value.trim()
    };

    const saved = await saveSettingsForInstance(activeInstanceKey, toSave);
    const reloaded = await getSettingsForInstance(saved.key);
    const verified =
      reloaded.settings.endpoint === saved.settings.endpoint &&
      reloaded.settings.phone === saved.settings.phone &&
      reloaded.settings.APIKEY === saved.settings.APIKEY &&
      reloaded.settings.crm_user_id === saved.settings.crm_user_id;

    renderSettings(reloaded.settings, reloaded.key, elements);

    if (!verified) {
      elements.status.textContent = "No se pudo verificar guardado.";
      console.error(`${LOG_PREFIX} Verificacion de guardado fallo`, {
        instanceKey: saved.key,
        expected: {
          endpoint: saved.settings.endpoint,
          phone: saved.settings.phone,
          apiKey: maskApiKey(saved.settings.APIKEY),
          crm_user_id: saved.settings.crm_user_id || null
        },
        actual: {
          endpoint: reloaded.settings.endpoint,
          phone: reloaded.settings.phone,
          apiKey: maskApiKey(reloaded.settings.APIKEY),
          crm_user_id: reloaded.settings.crm_user_id || null
        }
      });
      return;
    }

    elements.status.textContent = "Guardado.";
    console.info(`${LOG_PREFIX} Guardado verificado`, {
      instanceKey: saved.key,
      endpoint: saved.settings.endpoint,
      phone: saved.settings.phone,
      apiKey: maskApiKey(saved.settings.APIKEY),
      crm_user_id: saved.settings.crm_user_id || null
    });

    await renderRuntime(elements.runtime);
    setTimeout(() => (elements.status.textContent = ""), 1500);
  };

  document.getElementById("test").onclick = async () => {
    const endpoint = elements.endpointInput.value.trim() || DEFAULT_SETTINGS.endpoint;
    const APIKEY = elements.apiInput.value.trim();
    const phone = elements.phoneInput.value.trim();
    const crmUserId = elements.crmUserInput.value.trim();

    if (!/^https?:\/\//i.test(endpoint)) {
      elements.status.textContent = "Endpoint invalido. Debe iniciar con http:// o https://";
      return;
    }

    if (!APIKEY) {
      elements.status.textContent = "Falta APIKEY para probar.";
      return;
    }

    if (!phone) {
      elements.status.textContent = "Falta phone fallback para probar.";
      return;
    }

    elements.status.textContent = "Probando endpoint...";

    const payload = {
      id: `wa_crm_debug_${Date.now()}`,
      type: "chat",
      user: "wa_crm_extension_debug",
      instance_key: activeInstanceKey,
      phone,
      content: `Debug options ${new Date().toISOString()}`,
      APIKEY
    };

    if (/^\d+$/.test(crmUserId)) {
      payload.crm_user_id = Number(crmUserId);
    }

    try {
      const response = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        const body = await response.text();
        elements.status.textContent = `Error ${response.status}: ${body.slice(0, 160)}`;
        await renderRuntime(elements.runtime);
        return;
      }

      elements.status.textContent = `OK ${response.status}. Revisa logs del backend y cola.`;
      console.info(`${LOG_PREFIX} Test endpoint OK`, {
        instanceKey: activeInstanceKey,
        endpoint,
        phone,
        apiKey: maskApiKey(APIKEY)
      });
      await renderRuntime(elements.runtime);
    } catch (error) {
      elements.status.textContent = "Sin conexion al endpoint.";
      console.error(`${LOG_PREFIX} Test endpoint error`, {
        instanceKey: activeInstanceKey,
        endpoint,
        error: String(error)
      });
      await renderRuntime(elements.runtime);
    }
  };

  chrome.storage.onChanged.addListener(async (changes, areaName) => {
    if (areaName !== "local") {
      return;
    }

    if (changes[LAST_INSTANCE_KEY]) {
      const nextKey = String(changes[LAST_INSTANCE_KEY].newValue || "").trim() || DEFAULT_INSTANCE_KEY;
      if (nextKey !== activeInstanceKey) {
        activeInstanceKey = nextKey;
        const updated = await getSettingsForInstance(activeInstanceKey);
        renderSettings(updated.settings, updated.key, elements);
      }
    }

    if (changes.last_runtime_status) {
      await renderRuntime(elements.runtime);
    }
  });
});
