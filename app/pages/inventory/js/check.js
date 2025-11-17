let ultimaActualizacion = null;

async function checkForUpdates() {
  try {
    const res = await fetch("/DelixSystem/app/pages/inventory/php/utilidades/check_updates.php");
    const data = await res.json();

    if (!data.success) return;

    if (!ultimaActualizacion) {
      ultimaActualizacion = data.last_update;
      return;
    }

    if (data.last_update !== ultimaActualizacion) {
      ultimaActualizacion = data.last_update;

      await loadStorage();
      await reloadCategories();
    }

  } catch (err) {
    console.error("checkForUpdates ERROR:", err);
  }
}

setInterval(checkForUpdates, 4000);
