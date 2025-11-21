let ultimaActualizacionDish = null;

async function checkForDishUpdates() {
  try {
    const res = await fetch("/DelixSystem/app/pages/dishes_manager/php/utilidades/check_update.php");
    const data = await res.json();

    if (!data.success) return;

    if (!ultimaActualizacionDish) {
      ultimaActualizacionDish = data.last_update;
      return;
    }

    if (data.last_update !== ultimaActualizacionDish) {
      ultimaActualizacionDish = data.last_update;
      await loadDishes();
      await reloadDishCategories();
    }

  } catch (err) {
    console.error("checkForDishUpdates ERROR:", err);
  }
}

setInterval(checkForDishUpdates, 4000);
