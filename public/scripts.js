// Abrir y cerrar modales
const loginBtn = document.getElementById('loginBtn');
const registerBtn = document.getElementById('registerBtn');
const loginModal = document.getElementById('loginModal');
const registerModal = document.getElementById('registerModal');
const closeLogin = document.getElementById('closeLogin');
const closeRegister = document.getElementById('closeRegister');

loginBtn.onclick = () => loginModal.style.display = 'flex';
registerBtn.onclick = () => registerModal.style.display = 'flex';
closeLogin.onclick = () => loginModal.style.display = 'none';
closeRegister.onclick = () => registerModal.style.display = 'none';

// Cerrar modal al hacer clic fuera
window.onclick = (e) => {
    if (e.target === loginModal) loginModal.style.display = 'none';
    if (e.target === registerModal) registerModal.style.display = 'none';
};
