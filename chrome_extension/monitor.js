const OUTGOING_MESSAGES_KEY = "wa_crm_outgoing_messages";
const LAST_INSTANCE_KEY = "wa_crm_last_instance_key";
const DEFAULT_INSTANCE_KEY = "__default__";

let allEntries = [];
let activeInstanceKey = DEFAULT_INSTANCE_KEY;
let selectedFilter = "__all__";

const summarizeInstance = (instanceKey) => {
  const raw = String(instanceKey || "").trim();
  if (!raw) {
    return "-";
  }

  if (raw === DEFAULT_INSTANCE_KEY) {
    return "default";
  }

  return raw;
};

const formatDate = (value) => {
  const date = value ? new Date(value) : null;
  if (!date || Number.isNaN(date.getTime())) {
    return "-";
  }

  return date.toLocaleString();
};

const shortId = (value) => {
  const text = String(value || "").trim();
  if (!text) {
    return "-";
  }

  return text.length > 18 ? `${text.slice(0, 18)}...` : text;
};

const stateLabel = (state) => {
  const normalized = String(state || "").trim().toLowerCase();
  if (normalized === "queued") {
    return "queued";
  }

  if (normalized === "error") {
    return "error";
  }

  return "captured";
};

const getVisibleEntries = () => {
  if (selectedFilter === "__all__") {
    return allEntries;
  }

  if (selectedFilter === "__active__") {
    return allEntries.filter((entry) => {
      return String(entry.instance_key || DEFAULT_INSTANCE_KEY) === activeInstanceKey;
    });
  }

  return allEntries.filter((entry) => String(entry.instance_key || DEFAULT_INSTANCE_KEY) === selectedFilter);
};

const fillInstanceFilter = (selectEl) => {
  const knownValues = new Set(
    allEntries.map((entry) => String(entry.instance_key || DEFAULT_INSTANCE_KEY)).filter((value) => value)
  );
  knownValues.add(activeInstanceKey);

  const dynamicValues = [...knownValues]
    .filter((value) => value !== DEFAULT_INSTANCE_KEY)
    .sort((a, b) => a.localeCompare(b));

  const preserved = selectedFilter;
  selectEl.innerHTML = "";

  [
    { value: "__active__", label: "Instancia activa" },
    { value: "__all__", label: "Todas" },
    { value: DEFAULT_INSTANCE_KEY, label: "default" }
  ].forEach((item) => {
    const option = document.createElement("option");
    option.value = item.value;
    option.textContent = item.label;
    selectEl.appendChild(option);
  });

  dynamicValues.forEach((value) => {
    const option = document.createElement("option");
    option.value = value;
    option.textContent = value;
    selectEl.appendChild(option);
  });

  if ([...selectEl.options].some((option) => option.value === preserved)) {
    selectEl.value = preserved;
  } else {
    selectedFilter = "__all__";
    selectEl.value = "__all__";
  }
};

const renderRows = (elements) => {
  const rows = getVisibleEntries();
  elements.rows.innerHTML = "";

  if (!rows.length) {
    elements.empty.style.display = "block";
    elements.summary.textContent = `Instancia activa: ${summarizeInstance(activeInstanceKey)} · 0 mensajes`;
    return;
  }

  elements.empty.style.display = "none";

  rows.forEach((entry) => {
    const tr = document.createElement("tr");

    const tdTime = document.createElement("td");
    tdTime.textContent = formatDate(entry.updated_at || entry.created_at);
    tr.appendChild(tdTime);

    const tdState = document.createElement("td");
    const badge = document.createElement("span");
    const state = stateLabel(entry.state);
    badge.className = `badge state-${state}`;
    badge.textContent = state;
    tdState.appendChild(badge);
    tr.appendChild(tdState);

    const tdPhone = document.createElement("td");
    tdPhone.textContent = String(entry.phone || "-");
    tr.appendChild(tdPhone);

    const tdMessage = document.createElement("td");
    tdMessage.className = "message";
    tdMessage.textContent = String(entry.content || "").trim() || "(sin texto)";
    tdMessage.title = tdMessage.textContent;
    tr.appendChild(tdMessage);

    const tdInstance = document.createElement("td");
    tdInstance.textContent = summarizeInstance(entry.instance_key);
    tr.appendChild(tdInstance);

    const tdId = document.createElement("td");
    tdId.textContent = shortId(entry.id);
    tdId.title = String(entry.id || "");
    tr.appendChild(tdId);

    elements.rows.appendChild(tr);
  });

  elements.summary.textContent =
    `Instancia activa: ${summarizeInstance(activeInstanceKey)} · Mostrando ${rows.length} de ${allEntries.length} mensajes`;
};

const reloadFromStorage = async (elements) => {
  const stored = await chrome.storage.local.get([OUTGOING_MESSAGES_KEY, LAST_INSTANCE_KEY]);
  allEntries = Array.isArray(stored[OUTGOING_MESSAGES_KEY]) ? stored[OUTGOING_MESSAGES_KEY] : [];
  activeInstanceKey = String(stored[LAST_INSTANCE_KEY] || "").trim() || DEFAULT_INSTANCE_KEY;
  fillInstanceFilter(elements.instanceFilter);
  renderRows(elements);
};

document.addEventListener("DOMContentLoaded", async () => {
  const elements = {
    summary: document.getElementById("summary"),
    rows: document.getElementById("rows"),
    empty: document.getElementById("empty"),
    clear: document.getElementById("clear"),
    openConfig: document.getElementById("open_config"),
    instanceFilter: document.getElementById("instance_filter")
  };

  elements.instanceFilter.addEventListener("change", () => {
    selectedFilter = elements.instanceFilter.value;
    renderRows(elements);
  });

  elements.clear.addEventListener("click", async () => {
    await chrome.storage.local.set({
      [OUTGOING_MESSAGES_KEY]: []
    });
  });

  elements.openConfig.addEventListener("click", () => {
    window.open(chrome.runtime.getURL("options.html"), "_blank");
  });

  chrome.storage.onChanged.addListener(async (changes, areaName) => {
    if (areaName !== "local") {
      return;
    }

    if (changes[OUTGOING_MESSAGES_KEY] || changes[LAST_INSTANCE_KEY]) {
      await reloadFromStorage(elements);
    }
  });

  await reloadFromStorage(elements);
});
