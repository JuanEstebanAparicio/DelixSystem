let ultimaActualizacion = null;
let checking = false;

async function checkForUpdates() {
  if (checking) return;
  checking = true;

  try {
    const res = await fetch("/DelixSystem/app/pages/inventory/php/utilidades/check_updates.php");
    const data = await res.json();

    if (data.success) {

      if (!ultimaActualizacion) {
        ultimaActualizacion = data.last_update;
      } else if (data.last_update !== ultimaActualizacion) {
        ultimaActualizacion = data.last_update;

        await loadStorage();
        await reloadCategories();
      }

    }

  } catch (err) {
    console.error("checkForUpdates ERROR:", err);
  }

  checking = false;
}

setInterval(checkForUpdates, 4000);
