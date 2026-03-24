import { validateProgramme } from "./validation.js";

const API = "http://localhost/course_hub_api/api.php";

let editingId = null;
let programmes = [];

// DOM elements
const modal = document.getElementById("modal");
const formTitle = document.querySelector("#modal h3");
const nameInput = document.getElementById("name");
const levelSelect = document.getElementById("level");
const description = document.getElementById("description");
const formError = document.getElementById("formError");

// Load Programmes
async function loadProgrammes() {
  try {
    const res = await fetch(`${API}/?entity=programs`);
    const data = await res.json();

    programmes = data || [];
    renderTable();
  } catch (err) {
    console.error("Failed to load programmes:", err);
  }
}

// Render Table
function renderTable() {
  const table = document.getElementById("programmeTable");
  table.innerHTML = "";

  programmes.forEach((p) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${p.name}</td>
      <td>${p.level}</td>
      <td>${p.isPublished ? "Published" : "Draft"}</td>
      <td id="actions">
        <button class="edit-btn" data-id="${p.id}">Edit</button>
        <button class="toggle-btn" data-id="${p.id}">
          ${p.isPublished ? "Unpublish" : "Publish"}
        </button>
        <button class="delete-btn" data-id="${p.id}">Delete</button>
      </td>
    `;

    table.appendChild(row);
  });
}


// Open Form
function openForm() {
  editingId = null;
  formTitle.textContent = "Add Programme";

  nameInput.value = "";
  description.value = "";

  modal.classList.remove("hidden");
}

// Close Form
function closeForm() {
  modal.classList.add("hidden");
  formError.textContent = "";

  nameInput.value = "";
  description.value = "";
}

// Edit Programme
function editProgramme(id) {
  const p = programmes.find((x) => x.id == id);

  if (!p) return;

  editingId = id;

  nameInput.value = p.name;
  description.value = p.description;
  levelSelect.value = p.level

  formTitle.textContent = "Edit Programme";
  modal.classList.remove("hidden");
}

// Save Programme
async function saveProgramme() {
  const existing = programmes.find((p) => p.id == editingId);

  const data = {
    name: nameInput.value.trim(),
    level:levelSelect.value,
    description: description.value.trim(),
    isPublished: editingId ? existing?.isPublished : false
  };

  const error = validateProgramme(data);
  if (error) {
    formError.textContent = error;
    return;
  }

  try {
    let url = `${API}/?entity=programs`;
    let method = "POST";

    if (editingId) {
      url += `&id=${editingId}`;
      method = "PUT"; // change to POST if backend doesn't support PUT
    }

    const res = await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    });

    const result = await res.json();

    if (!res.ok) {
      throw new Error(result.message || "Failed to save programme");
    }

    closeForm();
    loadProgrammes();
  } catch (err) {
    console.error("Save error:", err);
    formError.textContent = err.message;
  }
}

// Delete Programme
async function deleteProgramme(id) {
  if (!confirm("Delete this programme?")) return;

  try {
    const res = await fetch(`${API}/?entity=programs&id=${id}`, {
      method: "DELETE"
    });

    const result = await res.json();

    if (!res.ok) {
      throw new Error(result.message || "Delete failed");
    }

    loadProgrammes();
  } catch (err) {
    console.error("Delete error:", err);
  }
}

// Toggle Publish
async function togglePublish(id) {
  const p = programmes.find((x) => x.id == id);
  if (!p) return;

  try {
    const res = await fetch(`${API}/?entity=programs&id=${id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: p.name,
        description: p.description,
        level:p.level,
        isPublished: !p.isPublished
      })
    });

    const result = await res.json();

    if (!res.ok) {
      throw new Error(result.message || "Toggle failed");
    }

    loadProgrammes();
  } catch (err) {
    console.error("Toggle error:", err);
  }
}

// Event Listeners
document.getElementById("programmeTable").addEventListener("click", (e) => {
  const btn = e.target;
  const id = btn.dataset.id;

  if (!id) return;

  if (btn.classList.contains("edit-btn")) {
    editProgramme(id);
  } else if (btn.classList.contains("toggle-btn")) {
    togglePublish(id);
  } else if (btn.classList.contains("delete-btn")) {
    deleteProgramme(id);
  }
});

document.getElementById("addProgrammeBtn").addEventListener("click", openForm);
document.getElementById("saveBtn").addEventListener("click", saveProgramme);
document.getElementById("cancelBtn").addEventListener("click", closeForm);

// Init
loadProgrammes();
