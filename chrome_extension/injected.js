(() => {
  const EVENT_MESSAGE = "WA_CRM_INTERNAL_MESSAGE";
  const EVENT_STATUS = "WA_CRM_INTERNAL_STATUS";
  const SEEN_MAX = 5000;
  const seenQueue = [];
  const seenSet = new Set();
  let attachedStore = null;

  const normalizePhone = (value) => String(value || "").replace(/\D+/g, "");
  const normalizeContent = (value) =>
    String(value || "")
      .replace(/\s+/g, " ")
      .trim()
      .slice(0, 4000);

  const emitStatus = (status, detail = {}) => {
    window.dispatchEvent(
      new CustomEvent(EVENT_STATUS, {
        detail: {
          source: "injected",
          status,
          ...detail
        }
      })
    );
  };

  const markSeen = (id) => {
    const key = String(id || "").trim();
    if (!key || seenSet.has(key)) {
      return false;
    }

    seenSet.add(key);
    seenQueue.push(key);

    if (seenQueue.length > SEEN_MAX) {
      const old = seenQueue.shift();
      if (old) {
        seenSet.delete(old);
      }
    }

    return true;
  };

  const pickExternalId = (msg) =>
    String(msg?.id?._serialized ?? msg?.id?.id ?? msg?.id ?? msg?.__x_id?._serialized ?? "").trim();

  const pickMessageType = (msg) => String(msg?.type ?? msg?.__x_type ?? "chat").toLowerCase().trim() || "chat";

  const pickMessageText = (msg) => {
    const baseText = normalizeContent(
      msg?.body ?? msg?.text ?? msg?.caption ?? msg?.__x_body ?? msg?.__x_text ?? msg?.__x_caption ?? ""
    );

    if (baseText) {
      return baseText;
    }

    const type = pickMessageType(msg);
    return type === "chat" ? "" : `[${type}]`;
  };

  const pickIsFromMe = (msg) =>
    Boolean(msg?.id?.fromMe ?? msg?.fromMe ?? msg?.isSentByMe ?? msg?.__x_isSentByMe ?? msg?.__x_fromMe ?? false);

  const pickTargetPhone = (msg) => {
    const raw =
      msg?.to?._serialized ??
      msg?.to?.user ??
      msg?.id?.remote?._serialized ??
      msg?.id?.remote?.user ??
      msg?.chat?._serialized ??
      "";

    return normalizePhone(raw);
  };

  const emitOutgoingMessage = (msg) => {
    if (!pickIsFromMe(msg)) {
      return;
    }

    const externalId = pickExternalId(msg);
    if (!externalId || !markSeen(externalId)) {
      return;
    }

    const content = pickMessageText(msg);
    if (!content) {
      return;
    }

    window.dispatchEvent(
      new CustomEvent(EVENT_MESSAGE, {
        detail: {
          source: "internal_store",
          id: externalId,
          type: pickMessageType(msg),
          phone: pickTargetPhone(msg),
          content
        }
      })
    );
  };

  const resolveWebpackChunkName = () => {
    const keys = Object.keys(window);
    for (const key of keys) {
      if (key.startsWith("webpackChunk") && Array.isArray(window[key])) {
        return key;
      }
    }

    return "";
  };

  const getWebpackRequire = () => {
    try {
      const chunkName = resolveWebpackChunkName();
      if (!chunkName) {
        emitStatus("waiting_chunk");
        return null;
      }

      let requireRef = null;
      const chunkId = `wa_crm_probe_${Date.now()}_${Math.floor(Math.random() * 10000)}`;
      window[chunkName].push([
        [chunkId],
        {},
        (requireFn) => {
          requireRef = requireFn;
        }
      ]);

      return requireRef;
    } catch (error) {
      emitStatus("webpack_require_error", { error: String(error) });
      return null;
    }
  };

  const scoreStoreCandidate = (candidate) => {
    if (!candidate || typeof candidate !== "object") {
      return 0;
    }

    if (typeof candidate.on !== "function") {
      return 0;
    }

    const models = candidate.models || candidate._models;
    if (!Array.isArray(models)) {
      return 1;
    }

    const sample = models[0];
    if (!sample || typeof sample !== "object") {
      return 2;
    }

    let score = 2;
    if (sample.id || sample.__x_id) {
      score += 2;
    }
    if (
      sample.body !== undefined ||
      sample.__x_body !== undefined ||
      sample.caption !== undefined ||
      sample.__x_caption !== undefined
    ) {
      score += 2;
    }
    if (sample.type !== undefined || sample.__x_type !== undefined) {
      score += 1;
    }

    return score;
  };

  const findMsgStore = (requireRef) => {
    const modules = Object.values(requireRef?.c || {});
    let best = null;
    let bestScore = 0;

    for (const module of modules) {
      const exportsObj = module?.exports;
      const candidates = [exportsObj, exportsObj?.default];

      for (const candidate of candidates) {
        const score = scoreStoreCandidate(candidate);
        if (score > bestScore) {
          best = candidate;
          bestScore = score;
        }
      }
    }

    if (bestScore < 3) {
      return null;
    }

    return best;
  };

  const attachToStore = (store) => {
    if (!store || typeof store.on !== "function") {
      return false;
    }

    if (attachedStore === store) {
      return true;
    }

    try {
      store.on("add", (msg) => {
        try {
          emitOutgoingMessage(msg);
        } catch (error) {
          emitStatus("message_process_error", { error: String(error) });
        }
      });

      attachedStore = store;
      emitStatus("attached", {
        hasModels: Array.isArray(store.models) || Array.isArray(store._models)
      });

      return true;
    } catch (error) {
      emitStatus("attach_error", { error: String(error) });
      return false;
    }
  };

  const tryAttach = () => {
    try {
      if (window.Store?.Msg && typeof window.Store.Msg.on === "function") {
        if (attachToStore(window.Store.Msg)) {
          return true;
        }
      }

      const requireRef = getWebpackRequire();
      if (!requireRef) {
        return false;
      }

      const store = findMsgStore(requireRef);
      if (!store) {
        emitStatus("waiting_store");
        return false;
      }

      return attachToStore(store);
    } catch (error) {
      emitStatus("try_attach_error", { error: String(error) });
      return false;
    }
  };

  emitStatus("boot");
  if (tryAttach()) {
    return;
  }

  let retries = 0;
  const maxRetries = 180;
  const interval = setInterval(() => {
    retries += 1;
    if (tryAttach()) {
      clearInterval(interval);
      return;
    }

    if (retries >= maxRetries) {
      clearInterval(interval);
      emitStatus("attach_timeout", { retries: maxRetries });
    }
  }, 1000);
})();
