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
});
