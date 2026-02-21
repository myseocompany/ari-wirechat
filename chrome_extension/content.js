(() => {
  const MAX_SEEN = 3000;
  const seenQueue = [];
  const seenSet = new Set();
  const DEFAULT_INSTANCE_KEY = "__default__";
  const BANNER_ID = "wa-crm-extension-banner";
  const LOG_PREFIX = "[wa-crm-content]";
  const INTERNAL_EVENT = "WA_CRM_INTERNAL_MESSAGE";
  const INTERNAL_STATUS_EVENT = "WA_CRM_INTERNAL_STATUS";

  const parseInstanceKey = (rawValue) => {
    const value = String(rawValue || "").trim();
    if (!value) {
      return "";
    }

    const unquoted = value.replace(/^"+|"+$/g, "");
    const fromSerialized = unquoted.match(/(\d{6,})@(?:s\.whatsapp\.net|c\.us)/);
    if (fromSerialized && fromSerialized[1]) {
      return `wa_${fromSerialized[1]}`;
    }

    const fromDigits = unquoted.replace(/\D+/g, "");
    if (fromDigits.length >= 6) {
      return `wa_${fromDigits}`;
    }

    return "";
  };

  const resolveInstanceKey = () => {
    try {
      const candidates = [
        window.localStorage.getItem("last-wid-md"),
        window.localStorage.getItem("last-wid"),
        window.localStorage.getItem("last-wid-browser")
      ];

      for (const candidate of candidates) {
        const parsed = parseInstanceKey(candidate);
        if (parsed) {
          return parsed;
        }
      }
    } catch (error) {
      console.warn(`${LOG_PREFIX} No se pudo leer localStorage para instancia`, error);
    }

    return DEFAULT_INSTANCE_KEY;
  };

  const INSTANCE_KEY = resolveInstanceKey();

  const sendRuntime = (reason, detail = null) => {
    chrome.runtime.sendMessage({
      type: "WA_CRM_RUNTIME",
      payload: {
        reason,
        detail: {
          ...(detail && typeof detail === "object" ? detail : {}),
          instance_key: INSTANCE_KEY
        }
      }
    });
  };

  const markSeen = (id) => {
    if (seenSet.has(id)) return false;
    seenSet.add(id);
    seenQueue.push(id);
    if (seenQueue.length > MAX_SEEN) {
      const old = seenQueue.shift();
      seenSet.delete(old);
    }
    return true;
  };

  const getMessageText = (msgNode) => {
    const firstTextNode = Array.from(msgNode.childNodes).find(
      (n) => n.nodeType === Node.TEXT_NODE && n.textContent.trim()
    );
    if (firstTextNode) return firstTextNode.textContent.trim();
    return msgNode.textContent.trim();
  };

  const getPhoneFromDataId = (dataId) => {
    const match = String(dataId).match(/^(?:true|false)_([^_]+)_/);
    if (!match) return "";

    const jid = match[1] || "";
    if (!jid.endsWith("@c.us") && !jid.endsWith("@s.whatsapp.net")) return "";

    return jid.replace(/\D+/g, "");
  };

  const normalizePhone = (value) => String(value || "").replace(/\D+/g, "");

  const injectInternalHook = () => {
    if (document.getElementById("wa-crm-injected-hook")) {
      return;
    }

    const script = document.createElement("script");
    script.id = "wa-crm-injected-hook";
    script.src = chrome.runtime.getURL("injected.js");
    script.async = false;
    script.onload = () => {
      script.remove();
      console.info(`${LOG_PREFIX} Hook interno inyectado`);
      sendRuntime("internal_hook_injected");
    };
    script.onerror = () => {
      console.error(`${LOG_PREFIX} Error inyectando hook interno`);
      sendRuntime("internal_hook_inject_error");
    };

    (document.head || document.documentElement).appendChild(script);
  };

  const showStatusBanner = (level, text) => {
    const existing = document.getElementById(BANNER_ID);
    if (existing) {
      existing.remove();
    }

    const banner = document.createElement("div");
    banner.id = BANNER_ID;
    banner.textContent = text;
    banner.style.position = "fixed";
    banner.style.top = "16px";
    banner.style.right = "16px";
    banner.style.zIndex = "999999";
    banner.style.maxWidth = "320px";
    banner.style.padding = "10px 12px";
    banner.style.borderRadius = "10px";
    banner.style.fontSize = "12px";
    banner.style.fontWeight = "600";
    banner.style.color = "#ffffff";
    banner.style.boxShadow = "0 8px 20px rgba(0,0,0,.25)";
    banner.style.background = level === "error" ? "#cc2f2f" : "#2f7d32";
    banner.style.pointerEvents = "none";

    document.body.appendChild(banner);
    setTimeout(() => banner.remove(), 5000);
  };

  const processMsgNode = (msgNode) => {
    if (!msgNode || !(msgNode instanceof HTMLElement)) return;

    const dataId = msgNode.getAttribute("data-id") || "";
    if (!dataId) return;

    const fromMe =
      dataId.startsWith("true_") ||
      msgNode.closest(".message-out") !== null ||
      msgNode.querySelector?.(".message-out") !== null;
    if (!fromMe) {
      markSeen(dataId);
      return;
    }

    if (!markSeen(dataId)) return;

    const phone = getPhoneFromDataId(dataId);
    const content = getMessageText(msgNode);
    console.info(`${LOG_PREFIX} Captura DOM`, {
      id: dataId,
      phone,
      hasContent: Boolean(content)
    });

    chrome.runtime.sendMessage({
      type: "OUTGOING_MESSAGE",
      payload: {
        id: dataId,
        type: "chat",
        user: "wa_crm_extension",
        instance_key: INSTANCE_KEY,
        phone,
        content
      }
    });
    sendRuntime("dom_capture", { id: dataId, phone });
  };

  const processInternalEvent = (detail) => {
    if (!detail || detail.source !== "internal_store") {
      return;
    }

    const externalId = String(detail.id || detail.externalId || "").trim();
    if (!externalId || !markSeen(externalId)) return;

    const content = String(detail.content || "").trim();
    if (!content) return;

    const phone = normalizePhone(detail.phone);
    console.info(`${LOG_PREFIX} Captura interna`, {
      id: externalId,
      phone,
      hasContent: Boolean(content)
    });

    chrome.runtime.sendMessage({
      type: "OUTGOING_MESSAGE",
      payload: {
        id: externalId,
        type: "chat",
        user: "wa_crm_extension",
        instance_key: INSTANCE_KEY,
        phone,
        content
      }
    });
    sendRuntime("internal_capture", { id: externalId, phone });
  };

  const extractMsgNodes = (root) => {
    const nodes = [];
    if (root instanceof HTMLElement && root.hasAttribute("data-id")) nodes.push(root);
    if (root instanceof HTMLElement) nodes.push(...root.querySelectorAll("[data-id]"));
    return nodes;
  };

  const primeSeen = (container) => {
    container.querySelectorAll("[data-id]").forEach((el) => {
      const id = el.getAttribute("data-id");
      if (id) markSeen(id);
    });
  };

  const boot = () => {
    const container = document.body;
    if (!container) return setTimeout(boot, 1000);
    console.info(`${LOG_PREFIX} Boot OK, observando DOM y hook interno`, {
      instanceKey: INSTANCE_KEY
    });
    sendRuntime("content_boot", { instance_key: INSTANCE_KEY });

    primeSeen(container);

    const mo = new MutationObserver((records) => {
      for (const record of records) {
        for (const added of record.addedNodes) {
          extractMsgNodes(added).forEach(processMsgNode);
        }
      }
    });

    mo.observe(container, { childList: true, subtree: true });
    injectInternalHook();
  };

  chrome.runtime.onMessage.addListener((message) => {
    if (message?.type !== "WA_CRM_STATUS") return;

    const text = String(message.payload?.text || "").trim();
    if (!text) return;

    const level = message.payload?.level === "error" ? "error" : "info";
    showStatusBanner(level, text);
  });

  window.addEventListener(INTERNAL_EVENT, (event) => {
    processInternalEvent(event?.detail);
  });

  window.addEventListener(INTERNAL_STATUS_EVENT, (event) => {
    const detail = event?.detail || {};
    console.info(`${LOG_PREFIX} Estado hook interno`, detail);
    sendRuntime("internal_hook_status", detail);
  });

  boot();
})();
