document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ DOM completamente cargado");

  // 🟢 Resalta el elemento activo del menú lateral
  document.querySelectorAll('.sidebar ul li').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.sidebar ul li').forEach(li => li.classList.remove('active'));
      item.classList.add('active');
    });
  });

  // ============================
  // 🧩 Lógica del sidebar plegable
  // ============================
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.getElementById("toggleSidebar");

  if (!sidebar) {
    console.error("❌ No se encontró el elemento .sidebar");
  } else {
    console.log("✅ Sidebar encontrado");
  }

  if (!toggleBtn) {
    console.error("❌ No se encontró el botón #toggleSidebar");
  } else {
    console.log("✅ Botón toggleSidebar encontrado");
  }

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("closed");
      console.log("🌀 Botón clickeado → Estado actual:", sidebar.classList.contains("closed") ? "CERRADO" : "ABIERTO");
    });
  }

  // ============================
  // 🧩 Lógica del modal perfil
  // ============================
  const modal = document.getElementById("profileModal");
  const openBtn = document.getElementById("openProfileModal");
  const closeBtn = modal?.querySelector(".close");
  const form = document.getElementById("profileForm");

  if (!modal || !openBtn || !closeBtn) {
    console.warn("⚠️ No se encontró el modal o sus botones correspondientes");
  } else {
    console.log("✅ Modal detectado correctamente");
  }

  // Abrir modal
  openBtn?.addEventListener("click", async () => {
    console.log("🟢 Icono de perfil clickeado");
    modal.style.display = "flex";

    try {
      const response = await fetch("../php/profile.php");
      if (!response.ok) throw new Error("Error al obtener perfil");
      const data = await response.json();

      document.getElementById("first_name").value = data.first_name || "";
      document.getElementById("last_name").value = data.last_name || "";
      document.getElementById("email").value = data.email || "";
      document.getElementById("restaurant_name").value = data.restaurant_name || "";
    } catch (err) {
      console.error("❌ Error al cargar perfil:", err);
    }
  });

  // Cerrar modal
  closeBtn?.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Cerrar modal al hacer clic fuera
  window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });

  // ============================
  // 🧩 Enviar formulario de perfil
  // ============================
  form?.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (form.dataset.submitting === "true") {
      Swal.fire({
        icon: "info",
        title: "Espera un momento",
        text: "Ya estás guardando los cambios...",
        timer: 1500,
        showConfirmButton: false
      });
      return;
    }

    form.dataset.submitting = "true";

    Swal.fire({
      title: "Guardando cambios...",
      text: "Por favor espera un momento.",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      const formData = new FormData(e.target);
      const response = await fetch("../php/profile.php", {
        method: "POST",
        body: formData
      });

      const data = await response.json();
      console.log("📨 Respuesta del servidor:", data);

      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "¡Perfil actualizado!",
          text: data.message,
          timer: 1500,
          showConfirmButton: false
        });

        modal.style.display = "none";
        setTimeout(() => {
          window.location.href = "../view/index.php";
        }, 1600);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar",
          text: data.error || "No se pudo actualizar el perfil."
        });
      }
    } catch (err) {
      console.error("❌ Error al actualizar perfil:", err);
      Swal.fire({
        icon: "error",
        title: "Error inesperado",
        text: "Ocurrió un problema al procesar tu solicitud."
      });
    } finally {
      setTimeout(() => {
        form.dataset.submitting = "false";
      }, 3000);
    }
  });

  console.log("✅ Script del dashboard cargado correctamente");
});
