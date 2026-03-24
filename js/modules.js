import { validateModule } from "./validation.js";

const API = "http://localhost/course_hub_api/api.php";

let modules = [];
let programmes = [];
let editingId = null;

// DOM
const programmeSelect = document.getElementById("programSelect");
const moduleNameInput = document.getElementById("moduleName");
const moduleDescriptionInput = document.getElementById("moduleDescription");
const moduleList = document.getElementById("moduleList");
const actionBtn = document.getElementById("moduleActionBtn");

// Load Programmes (REAL API)
async function loadProgrammes() {
  try {
    const res = await fetch(`${API}/?entity=programs`);
    programmes = await res.json();

    programmeSelect.innerHTML = `<option value="">Select Programme</option>`;

    programmes.forEach(p => {
      programmeSelect.innerHTML += `
        <option value="${p.id}">${p.name}</option>
      `;
    });

  } catch (err) {
    console.error("Failed to load programmes:", err);
  }
}

// Load Modules
async function loadModules() {
  try {
    const res = await fetch(`${API}/?entity=modules`);
    modules = await res.json();
  } catch (err) {
    console.error("Failed to load modules:", err);
    modules = [];
  }

  renderModules();
}

// Render Modules
function renderModules() {
  moduleList.innerHTML = "";

  if (modules.length === 0) {
    moduleList.innerHTML = "<li>No modules found</li>";
    return;
  }

  modules.forEach(m => {
    const programme = programmes.find(p => p.id == m.program_id);

    const li = document.createElement("li");
    li.className = "module-card";

    li.innerHTML = `
      <div class="module-card-body">
        <h3>${m.name}</h3>
        <p class="module-programme">
          ${programme ? programme.name : "Unknown Programme"}
        </p>
        <p class="module-description">
          ${m.description || "No description"}
        </p>
      </div>

      <div class="module-actions">
        <button class="edit-btn" data-id="${m.id}">✏️</button>
        <button class="delete-btn" data-id="${m.id}">🗑️</button>
      </div>
    `;

    moduleList.appendChild(li);
  });
}

// Add / Update Module
async function saveModule() {
  const data = {
    program_id: programmeSelect.value,
    name: moduleNameInput.value.trim(),
    description:moduleDescriptionInput.value.trim()
  };

  const error = validateModule(data);
  if (error) {
    alert(error);
    return;
  }

  try {
    let url = `${API}/?entity=modules`;
    let method = "POST";

    if (editingId) {
      url += `&id=${editingId}`;
      method = "PUT";
    }

    const res = await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    });

    const result = await res.json();

    if (!res.ok) {
      throw new Error(result.message || "Failed");
    }

    resetForm();
    loadModules();

  } catch (err) {
    console.error("Save module error:", err);
    alert(err.message);
  }
}

// Edit
function editModule(id) {
  const m = modules.find(x => x.id == id);
  if (!m) return;

  editingId = id;

  moduleNameInput.value = m.name;
  programmeSelect.value = m.program_id;
  moduleDescriptionInput.value = m.description;

  actionBtn.textContent = "Update Module";
}

// Delete
async function deleteModule(id) {
  if (!confirm("Delete this module?")) return;

  try {
    const res = await fetch(`${API}/?entity=modules&id=${id}`, {
      method: "DELETE"
    });

    const result = await res.json();

    if (!res.ok) {
      throw new Error(result.message || "Delete failed");
    }

    loadModules();

  } catch (err) {
    console.error("Delete error:", err);
  }
}

// Reset Form
function resetForm() {
  editingId = null;

  moduleNameInput.value = "";
  programmeSelect.value = "";
  moduleDescriptionInput.value = "";

  actionBtn.textContent = "Add Module";
}

// Event Delegation
moduleList.addEventListener("click", (e) => {
  const id = e.target.dataset.id;

  if (!id) return;

  if (e.target.classList.contains("edit-btn")) {
    editModule(id);
  } else if (e.target.classList.contains("delete-btn")) {
    deleteModule(id);
  }
});

//adding event listeners
actionBtn.addEventListener("click", saveModule);


// Init
loadProgrammes();
loadModules();