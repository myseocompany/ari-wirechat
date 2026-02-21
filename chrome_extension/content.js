(() => {
  const MAX_SEEN = 3000;
  const seenQueue = [];
  const seenSet = new Set();

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

  const findMessagesContainer = () =>
    document.querySelector('[data-scrolltracepolicy="wa.web.conversation.messages"]');

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

    chrome.runtime.sendMessage({
      type: "OUTGOING_MESSAGE",
      payload: {
        id: dataId,
        type: "chat",
        user: "sellerchat_extension",
        phone: getPhoneFromDataId(dataId),
        content: getMessageText(msgNode)
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
    const container = findMessagesContainer();
    if (!container) return setTimeout(boot, 1000);

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

  boot();
})();
