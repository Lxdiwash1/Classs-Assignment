const loginForm = document.getElementById("loginForm");
if(loginForm){


loginForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  if (!email || !password) {
    showError("All fields are required");
    return;
  }

  // Dummy auth (need to replace with API later)
  if (email === "admin@test.com" && password === "1234") {
    localStorage.setItem("auth", "true");
    window.location.href = "admin/admin-dashboard.html";
  } else {
    showError("Invalid credentials");
  }
});
}

const logoutBtn = document.getElementById("logoutbtn");

// Only run this if we are on a page with a logout button
if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    localStorage.removeItem("auth"); // Clear the "session"
    window.location.href = "/html/login.html"; // Redirect to login
  });
}

function showError(msg) {
  document.getElementById("error").textContent = msg;
}

