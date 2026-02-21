(() => {
  const EVENT_MESSAGE = "WA_CRM_INTERNAL_MESSAGE";
  const EVENT_STATUS = "WA_CRM_INTERNAL_STATUS";
  const SEEN_MAX = 5000;
  const seenQueue = [];
  const seenSet = new Set();

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
    if (!id || seenSet.has(id)) {
      return false;
    }

    seenSet.add(id);
    seenQueue.push(id);

    if (seenQueue.length > SEEN_MAX) {
      const old = seenQueue.shift();
      seenSet.delete(old);
    }

    return true;
  };

  const normalizePhone = (value) => String(value || "").replace(/\D+/g, "");

  const pickMessageText = (msg) =>
    String(
      msg?.body ??
        msg?.text ??
        msg?.caption ??
        msg?.__x_body ??
        msg?.__x_text ??
        msg?.__x_caption ??
        ""
    ).trim();

  const pickMessageType = (msg) =>
    String(msg?.type ?? msg?.__x_type ?? "").toLowerCase().trim();

  const pickExternalId = (msg) =>
    String(
      msg?.id?._serialized ??
        msg?.id?.id ??
        msg?.id ??
        msg?.__x_id?._serialized ??
        ""
    ).trim();

  const pickIsFromMe = (msg) =>
    Boolean(
      msg?.id?.fromMe ??
        msg?.fromMe ??
        msg?.isSentByMe ??
        msg?.__x_isSentByMe ??
        msg?.__x_fromMe ??
        false
    );

  const pickTargetPhone = (msg) => {
    const raw =
      msg?.to?._serialized ??
      msg?.to?.user ??
      msg?.id?.remote?._serialized ??
      msg?.id?.remote?.user ??
      msg?.chat?._serialized ??
      "";

    const phone = normalizePhone(raw);
    return phone;
  };

  const emitOutgoingMessage = (msg) => {
    const externalId = pickExternalId(msg);
    if (!externalId || !markSeen(externalId)) {
      return;
    }

    if (!pickIsFromMe(msg)) {
      return;
    }

    const type = pickMessageType(msg);
    if (type !== "chat" && type !== "buttons_response" && type !== "template_button_reply") {
      return;
    }

    const content = pickMessageText(msg);
    if (!content) {
      return;
    }

    const phone = pickTargetPhone(msg);

    window.dispatchEvent(
      new CustomEvent(EVENT_MESSAGE, {
        detail: {
          source: "internal_store",
          id: externalId,
          type: "chat",
          user: "wa_crm_extension",
          phone,
          content
        }
      })
    );
  };

  const pickCandidateStores = (requireRef) => {
    const stores = [];
    const modules = Object.values(requireRef?.c || {});

    for (const module of modules) {
      const exportsObj = module?.exports;
      if (!exportsObj) {
        continue;
      }

      const candidates = [exportsObj, exportsObj.default];
      for (const candidate of candidates) {
        if (!candidate || typeof candidate !== "object") {
          continue;
        }

        const hasEvents = typeof candidate.on === "function";
        const hasModelsArray = Array.isArray(candidate.models) || Array.isArray(candidate._models);
        if (!hasEvents || !hasModelsArray) {
          continue;
        }

        const sample = (candidate.models || candidate._models || [])[0];
        const looksLikeMessageModel =
          sample &&
          (sample.id || sample.__x_id) &&
          (sample.body !== undefined ||
            sample.__x_body !== undefined ||
            sample.type !== undefined ||
            sample.__x_type !== undefined);

        if (looksLikeMessageModel) {
          stores.push(candidate);
        }
      }
    }

    return stores;
  };

  const getWebpackRequire = () => {
    try {
      if (!Array.isArray(window.webpackChunkwhatsapp_web_client)) {
        return null;
      }

      let requireRef = null;
      const chunkId = "wa_crm_probe_" + Date.now();
      window.webpackChunkwhatsapp_web_client.push([
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

  const attachToStore = (store) => {
    if (!store || typeof store.on !== "function") {
      return false;
    }

    try {
      store.on("add", (msg) => {
        try {
          emitOutgoingMessage(msg);
        } catch (error) {
          emitStatus("message_process_error", { error: String(error) });
        }
      });

      emitStatus("attached", {
        hasModels: Array.isArray(store.models) || Array.isArray(store._models)
      });

      return true;
    } catch (error) {
      emitStatus("attach_error", { error: String(error) });
      return false;
    }
  };

  let attached = false;
  const tryAttach = () => {
    if (attached) {
      return;
    }

    try {
      if (window.Store?.Msg && typeof window.Store.Msg.on === "function") {
        attached = attachToStore(window.Store.Msg);
        if (attached) {
          return;
        }
      }

      const requireRef = getWebpackRequire();
      if (!requireRef) {
        emitStatus("waiting_require");
        return;
      }

      const stores = pickCandidateStores(requireRef);
      for (const store of stores) {
        attached = attachToStore(store);
        if (attached) {
          return;
        }
      }

      emitStatus("waiting_store", { candidates: stores.length });
    } catch (error) {
      emitStatus("try_attach_error", { error: String(error) });
    }
  };

  emitStatus("boot");
  tryAttach();

  const interval = setInterval(() => {
    tryAttach();
    if (attached) {
      clearInterval(interval);
    }
  }, 3000);
})();
