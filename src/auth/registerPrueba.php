<script>
document.querySelector('#registerModal form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const data = Object.fromEntries(new FormData(form).entries());
  
  const res = await fetch('./src/auth/registerPrueba.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  });

  const text = await res.text();
  alert(text); // Muestra "ok" o el error
});
</script>

