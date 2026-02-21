const ENDPOINT = "https://arichat.co/api/sellerchat/outgoing";

document.addEventListener("DOMContentLoaded", async () => {
  const phoneInput = document.getElementById("phone");
  const apiInput = document.getElementById("APIKEY");
  const crmUserInput = document.getElementById("crm_user_id");
  const status = document.getElementById("status");

  const { phone, APIKEY, crm_user_id } = await chrome.storage.sync.get([
    "phone",
    "APIKEY",
    "crm_user_id"
  ]);
  phoneInput.value = phone || "";
  apiInput.value = APIKEY || "";
  crmUserInput.value = crm_user_id || "";

  document.getElementById("save").onclick = async () => {
    await chrome.storage.sync.set({
      phone: phoneInput.value.trim(),
      APIKEY: apiInput.value.trim(),
      crm_user_id: crmUserInput.value.trim()
    });
    status.textContent = "Guardado.";
    setTimeout(() => (status.textContent = ""), 1500);
  };

  document.getElementById("test").onclick = async () => {
    const APIKEY = apiInput.value.trim();
    const phone = phoneInput.value.trim();
    const crmUserId = crmUserInput.value.trim();

    if (!APIKEY) {
      status.textContent = "Falta APIKEY para probar.";
      return;
    }

    if (!phone) {
      status.textContent = "Falta phone fallback para probar.";
      return;
    }

    status.textContent = "Probando endpoint...";

    const payload = {
      id: `sellerchat_debug_${Date.now()}`,
      type: "chat",
      user: "sellerchat_extension_debug",
      phone,
      content: `Debug options ${new Date().toISOString()}`,
      APIKEY
    };

    if (/^\d+$/.test(crmUserId)) {
      payload.crm_user_id = Number(crmUserId);
    }

    try {
      const response = await fetch(ENDPOINT, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        const body = await response.text();
        status.textContent = `Error ${response.status}: ${body.slice(0, 160)}`;
        return;
      }

      status.textContent = `OK ${response.status}. Revisa logs del backend y cola.`;
    } catch (error) {
      status.textContent = "Sin conexion al endpoint.";
    }
  };
});
