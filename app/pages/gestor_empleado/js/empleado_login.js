// 📂 gestor_empleado/js/empleado_login.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("empleadoAccessForm");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const res = await fetch("../php/employee/EmpleadoController.php", {
        method: "POST",
        body: formData,
      });

      const result = await res.json();
      console.log(result);

      if (result.status === "ok") {
        alert("✅ Welcome to the restaurant!");
        window.location.href = "../dashboard_employee.php";
      } else {
        alert("⚠️ " + result.message);
      }
    } catch (err) {
      alert("❌ Could not connect to the server");
    }
  });
});
