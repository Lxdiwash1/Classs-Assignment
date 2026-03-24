// Redirect if not logged in
(function () {
  console.log("guard file")
  const isAuth = localStorage.getItem("auth");
  console.log("isAuth",isAuth)

  if (isAuth == null || false) {
    window.location.href = "login.html";
  }
})();