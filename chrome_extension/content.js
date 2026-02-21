(() => {
  const MAX_SEEN = 3000;
  const seenQueue = [];
  const seenSet = new Set();
  const BANNER_ID = "sellerchat-extension-banner";
  const LOG_PREFIX = "[sellerchat-content]";

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

    const fromMe = dataId.startsWith("true_");
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
        user: "sellerchat_extension",
        phone,
        content
      }
    });
  };

  const processToAngularEvent = (detail) => {
    if (!detail || detail.action !== "newMsg" || !detail.isFromMe) return;

    const externalId = String(detail.id || "").trim();
    if (!externalId || !markSeen(externalId)) return;

    const type = String(detail.type || "").toLowerCase();
    if (type !== "chat" && type !== "buttons_response" && type !== "template_button_reply") {
      return;
    }

    const content = String(detail.text || detail.content || "").trim();
    if (!content) return;

    const phone = normalizePhone(detail.sender);
    console.info(`${LOG_PREFIX} Captura toAngular`, {
      id: externalId,
      type,
      phone,
      hasContent: Boolean(content)
    });

    chrome.runtime.sendMessage({
      type: "OUTGOING_MESSAGE",
      payload: {
        id: externalId,
        type: "chat",
        user: "sellerchat_extension",
        phone,
        content
      }
    });
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
    console.info(`${LOG_PREFIX} Boot OK, observando DOM y eventos toAngular`);

    primeSeen(container);

    const mo = new MutationObserver((records) => {
      for (const record of records) {
        for (const added of record.addedNodes) {
          extractMsgNodes(added).forEach(processMsgNode);
        }
      }
    });

    mo.observe(container, { childList: true, subtree: true });
  };

  chrome.runtime.onMessage.addListener((message) => {
    if (message?.type !== "SELLERCHAT_STATUS") return;

    const text = String(message.payload?.text || "").trim();
    if (!text) return;

    const level = message.payload?.level === "error" ? "error" : "info";
    showStatusBanner(level, text);
  });

  window.addEventListener("toAngular", (event) => {
    processToAngularEvent(event?.detail);
  });

  boot();
})();
