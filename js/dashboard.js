const API = "http://localhost/course_hub_api/api.php";

async function loadDashboard() {
  try {
    const progRes = await fetch(`${API}/?entity=programs`);
    const programs = await progRes.json();

    document.getElementById("progCount").textContent = programs.length;

    const modRes = await fetch(`${API}/?entity=modules`);
    const modules = await modRes.json();

    document.getElementById("moduleCount").textContent = modules.length;

    const publishedCount = programs.filter(p => p.isPublished).length;
    const unpublishedCount = programs.filter(p => !p.isPublished).length;

    document.getElementById("publishedCount").textContent = publishedCount;
    document.getElementById("draftCount").textContent = unpublishedCount;

  } catch (err) {
    console.error("Dashboard load error:", err);
  }
}

loadDashboard();