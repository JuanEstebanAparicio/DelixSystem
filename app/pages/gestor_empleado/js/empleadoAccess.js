// empleadosAccess.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("empleadoAccessForm");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = new FormData(form);

    try {
      const res = await fetch("/ProjectDelix/app/pages/gestor_empleado/php/employee/EmpleadoController.php", {
        method: "POST",
        body: data,
      });

      const result = await res.json();

      if (result.status === "success") {
        alert("✅ Employee registered successfully!");
        form.reset();
      } else {
        alert("❌ " + result.message);
      }
    } catch (err) {
      console.error(err);
      alert("⚠️ Server error, please try again later.");
    }
  });
});
