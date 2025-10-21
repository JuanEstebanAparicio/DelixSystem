// =============================
// 🔐 Dynamic Code Manager
// =============================
const codeDisplay = document.getElementById("codigoDinamico");
const estadoCodigo = document.getElementById("estadoCodigo");
const timerText = document.getElementById("timerText");
const btnNuevoCodigo = document.getElementById("nuevoCodigo");
const btnCopiar = document.getElementById("copiarCodigo");

let countdownInterval = null;
const userId = 1; // 👈 Replace with PHP session value dynamically

// ✅ Fetch existing code or create new one
async function fetchActiveCode() {
  const res = await fetch("../php/Key/DynamicKeyController.php", {
    method: "POST",
    body: new URLSearchParams({
      action: "get",
      user_id: userId,
    }),
  });
  const data = await res.json();

  if (data.status === "ok") {
    startCountdown(data.code, data.expires_at);
  } else if (data.status === "no_active") {
    await generateNewCode(); // auto-generate if none active
  }
}

// ✅ Generate a new code
async function generateNewCode() {
  const res = await fetch("../php/Key/DynamicKeyController.php", {
    method: "POST",
    body: new URLSearchParams({
      action: "generate",
      user_id: userId,
    }),
  });
  const data = await res.json();

  if (data.status === "ok") {
    startCountdown(data.code, data.expires_at);
  } else {
    console.error("Failed to generate code:", data);
  }
}

// ✅ Start countdown and auto-regenerate when expired
function startCountdown(code, expiresAt) {
  clearInterval(countdownInterval);

  // Show code
  codeDisplay.textContent = code;
  estadoCodigo.textContent = "Código activo";
  estadoCodigo.className = "estado activo";

  const expireTime = new Date(expiresAt).getTime();

  function updateTimer() {
    const now = new Date().getTime();
    const diff = Math.floor((expireTime - now) / 1000);

    if (diff <= 0) {
      estadoCodigo.textContent = "Código expirado";
      estadoCodigo.className = "estado expirado";
      codeDisplay.textContent = "••••••••";
      clearInterval(countdownInterval);

      // ⏱️ Auto-generate a new one
      setTimeout(generateNewCode, 1000);
      return;
    }

    timerText.textContent = diff + "s";

    // Optional: Update SVG circle (if you want visual countdown)
    const circle = document.querySelector(".progress");
    const percentage = (diff / 60) * 283; // 283 = circle length
    circle.style.strokeDashoffset = 283 - percentage;
  }

  updateTimer();
  countdownInterval = setInterval(updateTimer, 1000);
}

// ✅ Manual regenerate button
btnNuevoCodigo.addEventListener("click", async () => {
  await generateNewCode();
});

// ✅ Copy button
btnCopiar.addEventListener("click", () => {
  const text = codeDisplay.textContent;
  if (text && text !== "••••••••") {
    navigator.clipboard.writeText(text);
    btnCopiar.textContent = "Copied!";
    setTimeout(() => (btnCopiar.textContent = "Copy"), 2000);
  }
});

// ✅ Init on page load
document.addEventListener("DOMContentLoaded", fetchActiveCode);
