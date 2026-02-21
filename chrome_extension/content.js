(() => {
  const MAX_SEEN = 5000;
  const SEEN_QUEUE = [];
  const SEEN_SET = new Set();
  const DEFAULT_INSTANCE_KEY = "__default__";
  const LOG_PREFIX = "[wa-crm-content]";
  const INTERNAL_EVENT = "WA_CRM_INTERNAL_MESSAGE";
  const INTERNAL_STATUS_EVENT = "WA_CRM_INTERNAL_STATUS";

  const normalizePhone = (value) => String(value || "").replace(/\D+/g, "");
  const normalizeContent = (value) =>
    String(value || "")
      .replace(/\s+/g, " ")
      .trim()
      .slice(0, 4000);

  const markSeen = (id) => {
    const key = String(id || "").trim();
    if (!key || SEEN_SET.has(key)) {
      return false;
    }

    SEEN_SET.add(key);
    SEEN_QUEUE.push(key);
    if (SEEN_QUEUE.length > MAX_SEEN) {
      const old = SEEN_QUEUE.shift();
      if (old) {
        SEEN_SET.delete(old);
      }
    }

    return true;
  };

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
      console.warn(`${LOG_PREFIX} No se pudo resolver instancia`, error);
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

  const getPhoneFromDataId = (dataId) => {
    const match = String(dataId || "").match(/^(?:true|false)_([^_]+)_/);
    if (!match) {
      return "";
    }

    return normalizePhone(match[1] || "");
  };

  const extractContentFromNode = (node) => {
    if (!(node instanceof HTMLElement)) {
      return "";
    }

    const copyable = node.querySelector("[data-pre-plain-text]");
    const textNode =
      copyable?.querySelector(".selectable-text.copyable-text span[dir]") ||
      copyable?.querySelector(".selectable-text span[dir]") ||
      copyable?.querySelector(".copyable-text span[dir]") ||
      copyable?.querySelector("span[dir]") ||
      node.querySelector(".selectable-text.copyable-text span[dir]") ||
      node.querySelector(".selectable-text span[dir]") ||
      node.querySelector(".copyable-text span[dir]") ||
      node;

    return normalizeContent(textNode?.textContent || "");
  };

  const isLikelyOutgoingNode = (node, dataId) => {
    const idText = String(dataId || "");
    if (idText.startsWith("true_")) {
      return true;
    }

    if (node.closest(".message-out")) {
      return true;
    }

    const testId = node.closest("[data-testid]")?.getAttribute("data-testid") || "";
    if (/outgoing|msg-out|message-out/i.test(testId)) {
      return true;
    }

    const plainTextHint = node.querySelector("[data-pre-plain-text]")?.getAttribute("data-pre-plain-text") || "";
    if (/\bYou:\b/i.test(plainTextHint)) {
      return true;
    }

    return false;
  };

  const sendOutgoing = ({ id, type = "chat", phone = "", content = "" }, source) => {
    const messageId = String(id || "").trim();
    const normalizedContent = normalizeContent(content);
    if (!messageId || !normalizedContent) {
      return;
    }

    if (!markSeen(messageId)) {
      return;
    }

    const normalizedPhone = normalizePhone(phone);
    chrome.runtime.sendMessage({
      type: "OUTGOING_MESSAGE",
      payload: {
        id: messageId,
        type: String(type || "chat").trim() || "chat",
        user: "wa_crm_extension",
        instance_key: INSTANCE_KEY,
        phone: normalizedPhone,
        content: normalizedContent
      }
    });

    sendRuntime("outgoing_captured", {
      source,
      id: messageId,
      phone: normalizedPhone,
      content_preview: normalizedContent.slice(0, 120)
    });
  };

  const processInternalEvent = (detail) => {
    if (!detail || detail.source !== "internal_store") {
      return;
    }

    sendOutgoing(
      {
        id: detail.id || detail.externalId || "",
        type: detail.type || "chat",
        phone: detail.phone || "",
        content: detail.content || ""
      },
      "internal_store"
    );
  };

  const scanOutgoingFromDom = () => {
    const nodes = document.querySelectorAll("[data-id]");
    nodes.forEach((node) => {
      const dataId = node.getAttribute("data-id") || "";
      if (!dataId || SEEN_SET.has(dataId)) {
        return;
      }

      if (!isLikelyOutgoingNode(node, dataId)) {
        if (dataId.startsWith("false_")) {
          markSeen(dataId);
        }
        return;
      }

      const content = extractContentFromNode(node);
      if (!content) {
        return;
      }

      sendOutgoing(
        {
          id: dataId,
          type: "chat",
          phone: getPhoneFromDataId(dataId),
          content
        },
        "dom_fallback"
      );
    });
  };

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
      sendRuntime("internal_hook_injected");
    };
    script.onerror = () => {
      sendRuntime("internal_hook_inject_error");
    };

    (document.head || document.documentElement).appendChild(script);
  };

  const boot = () => {
    if (!document.body) {
      setTimeout(boot, 1000);
      return;
    }

    sendRuntime("content_boot", { instance_key: INSTANCE_KEY });
    injectInternalHook();
    scanOutgoingFromDom();

    const observer = new MutationObserver(() => {
      scanOutgoingFromDom();
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["data-id", "class", "data-testid"]
    });

    setInterval(scanOutgoingFromDom, 1500);
  };

  window.addEventListener(INTERNAL_EVENT, (event) => {
    processInternalEvent(event?.detail);
  });

  window.addEventListener(INTERNAL_STATUS_EVENT, (event) => {
    sendRuntime("internal_hook_status", event?.detail || {});
  });

  boot();
})();
