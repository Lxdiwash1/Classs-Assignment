// Redirect if not logged in
(function () {
  const isAuth = localStorage.getItem("auth");

  if (!isAuth) {
    window.location.href = "login.html";
  }
})();